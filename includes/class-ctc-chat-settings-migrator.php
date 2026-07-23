<?php
/**
 * Versioned migration for the Click to Chat settings option.
 *
 * @package ClickToChat
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Normalize released settings into the current schema without losing merchant data.
 */
class CTC_Chat_Settings_Migrator {

	const TARGET_SCHEMA = '1.0.0';
	const OPTION_NAME = 'ctc_chat_settings';
	const VERSION_OPTION = 'ctc_chat_settings_schema_version';
	const ERROR_OPTION = 'ctc_chat_settings_migration_error';

	/**
	 * Run the idempotent settings migration.
	 *
	 * @param array|null $defaults Current plugin defaults.
	 * @return string Stable result code.
	 */
	public static function migrate( $defaults = null ) {
		if ( null === $defaults && function_exists( 'ctc_chat_get_default_settings' ) ) {
			$defaults = ctc_chat_get_default_settings();
		}
		$defaults = is_array( $defaults ) ? $defaults : array();
		$raw      = get_option( self::OPTION_NAME, false );

		if ( false === $raw ) {
			$normalized = self::merge_defaults( $defaults, array() );
			if ( ! update_option( self::OPTION_NAME, $normalized ) ) {
				self::record_error( 'settings_write_failed', 'missing' );
				return 'settings_write_failed';
			}
			self::complete_success();
			return 'fresh_install';
		}

		if ( ! is_array( $raw ) ) {
			self::record_error( 'malformed_settings', 'malformed' );
			return 'malformed_settings';
		}

		$normalized = self::normalize_top_level( $raw );
		$normalized = self::merge_defaults( $defaults, $normalized );

		if ( $normalized !== $raw ) {
			if ( ! update_option( self::OPTION_NAME, $normalized ) ) {
				self::record_error( 'settings_write_failed', self::detect_schema( $raw ) );
				return 'settings_write_failed';
			}
			self::complete_success();
			return 'migrated';
		}

		if ( self::TARGET_SCHEMA !== (string) get_option( self::VERSION_OPTION, '' ) ) {
			update_option( self::VERSION_OPTION, self::TARGET_SCHEMA );
		}
		delete_option( self::ERROR_OPTION );
		return 'unchanged';
	}

	/**
	 * Determine the stored schema family for diagnostics.
	 *
	 * @param array $settings Settings record.
	 * @return string
	 */
	public static function detect_schema( $settings ) {
		$legacy = false;
		$canonical = false;
		foreach ( self::top_level_map() as $legacy_key => $canonical_key ) {
			$legacy    = $legacy || array_key_exists( $legacy_key, $settings );
			$canonical = $canonical || array_key_exists( $canonical_key, $settings );
		}
		if ( $legacy && $canonical ) {
			return 'mixed';
		}
		if ( $legacy ) {
			return 'legacy';
		}
		return 'canonical';
	}

	/** @return array<string,string> */
	private static function top_level_map() {
		return array(
			'ctc_chat_plugin_enabled'    => 'plugin_enabled',
			'ctc_chat_whatsapp_numbers'  => 'whatsapp_numbers',
			'ctc_chat_button_settings'   => 'button_settings',
			'ctc_chat_single_product'    => 'single_product',
			'ctc_chat_shop_page'         => 'shop_page',
			'ctc_chat_cart_page'         => 'cart_page',
			'ctc_chat_checkout_page'     => 'checkout_page',
			'ctc_chat_thankyou_page'     => 'thankyou_page',
			'ctc_chat_floating_button'   => 'floating_button',
			'ctc_chat_message_templates' => 'message_templates',
			'ctc_chat_exclusions'        => 'exclusions',
			'ctc_chat_advanced'          => 'advanced',
		);
	}

	/** @return array<string,string> */
	private static function nested_map() {
		$map = array(
			'ctc_chat_text'               => 'text',
			'ctc_chat_icon'               => 'icon',
			'ctc_chat_bg_color'           => 'bg_color',
			'ctc_chat_text_color'         => 'text_color',
			'ctc_chat_enabled'            => 'enabled',
			'ctc_chat_position'           => 'position',
			'ctc_chat_id'                 => 'id',
			'ctc_chat_name'               => 'name',
			'ctc_chat_number'             => 'number',
			'ctc_chat_description'        => 'description',
			'ctc_chat_is_default'         => 'is_default',
			'ctc_chat_assignments'        => 'assignments',
			'ctc_chat_products'           => 'products',
			'ctc_chat_categories'         => 'categories',
			'ctc_chat_pages'              => 'pages',
			'ctc_chat_posts'              => 'posts',
			'ctc_chat_tags'               => 'tags',
			'ctc_chat_hide_add_to_cart'   => 'hide_add_to_cart',
			'ctc_chat_hide_proceed_checkout' => 'hide_proceed_checkout',
			'ctc_chat_hide_place_order'   => 'hide_place_order',
			'ctc_chat_catalog_mode'       => 'catalog_mode',
			'ctc_chat_single_product'     => 'single_product',
			'ctc_chat_cart_checkout'      => 'cart_checkout',
			'ctc_chat_thank_you'          => 'thank_you',
			'ctc_chat_floating'           => 'floating',
			'ctc_chat_variations'         => 'variations',
		);

		/*
		 * A previous faulty migration could leave canonical child names inside a
		 * recognized legacy container. Treat those names as identity aliases so a
		 * corrected run can recover the original ordered records automatically.
		 */
		foreach ( array_values( $map ) as $canonical_key ) {
			$map[ $canonical_key ] = $canonical_key;
		}

		return $map;
	}

	/**
	 * Normalize top-level groups and retain unknown legacy descendants in shells.
	 *
	 * @param array $settings Raw settings.
	 * @return array
	 */
	private static function normalize_top_level( $settings ) {
		$result = $settings;
		$top_map = self::top_level_map();
		foreach ( $top_map as $legacy_key => $canonical_key ) {
			if ( ! array_key_exists( $legacy_key, $settings ) ) {
				continue;
			}
			list( $mapped, $unknown ) = self::map_container( $settings[ $legacy_key ] );
			if ( ! array_key_exists( $canonical_key, $result ) ) {
				$result[ $canonical_key ] = $mapped;
			} elseif ( is_array( $result[ $canonical_key ] ) && is_array( $mapped ) ) {
				$result[ $canonical_key ] = self::merge_missing( $result[ $canonical_key ], $mapped );
			}
			if ( self::has_values( $unknown ) ) {
				$result[ $legacy_key ] = $unknown;
			} else {
				unset( $result[ $legacy_key ] );
			}
		}

		foreach ( $top_map as $legacy_key => $canonical_key ) {
			if ( array_key_exists( $canonical_key, $result ) && is_array( $result[ $canonical_key ] ) ) {
				list( $mapped, $unused ) = self::map_container( $result[ $canonical_key ] );
				$result[ $canonical_key ] = self::merge_missing( $result[ $canonical_key ], $mapped );
			}
		}
		return $result;
	}

	/** @return array{0:mixed,1:mixed} */
	private static function map_container( $value ) {
		if ( ! is_array( $value ) ) {
			return array( $value, array() );
		}
		$map = self::nested_map();
		$mapped = array();
		$unknown = array();
		foreach ( $value as $key => $item ) {
			$is_list_index = is_int( $key );
			$target = array_key_exists( $key, $map ) ? $map[ $key ] : $key;
			$child_unknown = array();
			if ( is_array( $item ) ) {
				list( $child_mapped, $child_unknown ) = self::map_container( $item );
				$item = $child_mapped;
			}
			if ( $is_list_index ) {
				/*
				 * Numeric keys are structural list indexes, not setting names. They must
				 * exist in the canonical result or ordered number records disappear.
				 * Retain an indexed legacy shell only when that record has unknown fields.
				 */
				$mapped[ $key ] = $item;
				if ( self::has_values( $child_unknown ) ) {
					$unknown[ $key ] = $child_unknown;
				}
			} elseif ( array_key_exists( $key, $map ) ) {
				if ( self::has_values( $child_unknown ) ) {
					$unknown[ $key ] = $child_unknown;
				}
				if ( ! array_key_exists( $target, $mapped ) ) {
					$mapped[ $target ] = $item;
				}
			} else {
				$unknown[ $key ] = self::has_values( $child_unknown ) ? $child_unknown : $item;
			}
		}
		return array( $mapped, $unknown );
	}

	private static function merge_missing( $current, $incoming ) {
		foreach ( $incoming as $key => $value ) {
			if ( ! array_key_exists( $key, $current ) ) {
				$current[ $key ] = $value;
			} elseif ( is_array( $current[ $key ] ) && is_array( $value ) ) {
				$current[ $key ] = self::merge_missing( $current[ $key ], $value );
			}
		}
		return $current;
	}

	private static function merge_defaults( $defaults, $current ) {
		foreach ( $defaults as $key => $value ) {
			if ( is_array( $value ) ) {
				$child = array_key_exists( $key, $current ) && is_array( $current[ $key ] ) ? $current[ $key ] : array();
				$current[ $key ] = self::merge_defaults( $value, $child );
			} elseif ( ! array_key_exists( $key, $current ) ) {
				$current[ $key ] = $value;
			}
		}
		return $current;
	}

	private static function has_values( $value ) {
		return is_array( $value ) ? ! empty( $value ) : null !== $value;
	}

	private static function complete_success() {
		update_option( self::VERSION_OPTION, self::TARGET_SCHEMA );
		delete_option( self::ERROR_OPTION );
	}

	private static function record_error( $code, $source_schema ) {
		update_option(
			self::ERROR_OPTION,
			array(
				'code'         => sanitize_key( $code ),
				'source_schema' => sanitize_key( $source_schema ),
				'target_schema' => self::TARGET_SCHEMA,
				'recorded_at'   => current_time( 'mysql' ),
			)
		);
	}
}
