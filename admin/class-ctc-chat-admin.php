<?php
/**
 * Admin class.
 *
 * This class handles all admin-related functionality.
 *
 * @package ClickToChat
 * @since 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin class.
 *
 * @since 1.0.0
 * @package CTC_Chat
 */
class CTC_Chat_Admin {

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $ctc_chat_settings;

	/**
	 * Flag to track if settings page has been rendered.
	 *
	 * @var bool
	 */
	private static $ctc_chat_settings_page_rendered = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->ctc_chat_settings = get_option( 'ctc_chat_settings', array() );

		// Initialize hooks.
		$this->ctc_chat_init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function ctc_chat_init_hooks() {
		// Add admin menu.
		add_action( 'admin_menu', array( $this, 'ctc_chat_add_admin_menu' ) );

		// Register admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'ctc_chat_enqueue_admin_assets' ) );

		// Add plugin action links.
		add_filter( 'plugin_action_links_' . CTC_CHAT_PLUGIN_BASENAME, array( $this, 'ctc_chat_add_action_links' ) );

		// Add meta box to product edit screen.
		add_action( 'add_meta_boxes', array( $this, 'ctc_chat_add_product_meta_boxes' ) );

		// Save product meta.
		add_action( 'save_post_product', array( $this, 'ctc_chat_save_product_meta' ), 10, 2 );
	}

	/**
	 * Add admin menu.
	 */
	public function ctc_chat_add_admin_menu() {
		add_menu_page(
			esc_html__( 'Click to Chat', 'aicoso-click-to-chat' ),
			esc_html__( 'Click to Chat', 'aicoso-click-to-chat' ),
			'manage_options',
			'click-to-chat',
			array( $this, 'ctc_chat_render_settings_page' ),
			'dashicons-whatsapp', // Custom dashicon for WhatsApp.
			58 // Position after WooCommerce.
		);

		// Add submenus.
		add_submenu_page(
			'click-to-chat',
			esc_html__( 'Settings', 'aicoso-click-to-chat' ),
			esc_html__( 'Settings', 'aicoso-click-to-chat' ),
			'manage_options',
			'click-to-chat',
			array( $this, 'ctc_chat_render_settings_page' )
		);

		add_submenu_page(
			'click-to-chat',
			esc_html__( 'WhatsApp Numbers', 'aicoso-click-to-chat' ),
			esc_html__( 'WhatsApp Numbers', 'aicoso-click-to-chat' ),
			'manage_options',
			'click-to-chat-numbers',
			array( $this, 'ctc_chat_render_numbers_page' )
		);

		add_submenu_page(
			'click-to-chat',
			esc_html__( 'Message Templates', 'aicoso-click-to-chat' ),
			esc_html__( 'Message Templates', 'aicoso-click-to-chat' ),
			'manage_options',
			'click-to-chat-templates',
			array( $this, 'ctc_chat_render_templates_page' )
		);

		add_submenu_page(
			'click-to-chat',
			esc_html__( 'Shortcode Generator', 'aicoso-click-to-chat' ),
			esc_html__( 'Shortcode Generator', 'aicoso-click-to-chat' ),
			'manage_options',
			'click-to-chat-shortcodes',
			array( $this, 'ctc_chat_render_shortcode_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function ctc_chat_enqueue_admin_assets( $hook ) {
		// Only enqueue on plugin admin pages.
		if ( strpos( $hook, 'click-to-chat' ) === false ) {
			return;
		}

		// CSS.
		wp_enqueue_style( 'wp-color-picker' );

		// Enqueue Select2 from local files.
		wp_enqueue_style(
			'ctc-chat-select2',
			CTC_CHAT_PLUGIN_URL . 'admin/lib/select2/select2.min.css',
			array(),
			'4.0.13'
		);

		wp_enqueue_style(
			'ctc-chat-admin-styles',
			CTC_CHAT_PLUGIN_URL . 'admin/css/admin.css',
			array( 'wp-color-picker', 'ctc-chat-select2' ),
			CTC_CHAT_VERSION
		);

		// JavaScript.
		// Enqueue Select2 from local files.
		wp_enqueue_script(
			'ctc-chat-select2-js',
			CTC_CHAT_PLUGIN_URL . 'admin/lib/select2/select2.min.js',
			array( 'jquery' ),
			'4.0.13',
			true
		);

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-sortable' );

		wp_enqueue_script(
			'ctc-chat-admin-js',
			CTC_CHAT_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery', 'wp-color-picker', 'ctc-chat-select2-js', 'jquery-ui-sortable' ),
			CTC_CHAT_VERSION,
			true
		);

		// Pass variables to JavaScript.
		wp_localize_script(
			'ctc-chat-admin-js',
			'ctc_chat_admin',
			array(
				'ajaxurl'                => admin_url( 'admin-ajax.php' ),
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
				'nonce'                  => wp_create_nonce( 'ctc_chat_admin_nonce' ),
				'delete_number_confirm'  => esc_html__( 'Are you sure you want to delete this WhatsApp number?', 'aicoso-click-to-chat' ),
				'duplicate_name_error'   => esc_html__( 'This name is already being used. Please choose a different name.', 'aicoso-click-to-chat' ),
				'loading_text'           => esc_html__( 'Loading...', 'aicoso-click-to-chat' ),
				'preview_text'           => esc_html__( 'Preview', 'aicoso-click-to-chat' ),
				'default_button_text'    => esc_html__( 'Order via WhatsApp', 'aicoso-click-to-chat' ),
				'copy_success'           => esc_html__( 'Shortcode copied to clipboard!', 'aicoso-click-to-chat' ),
				'copy_error'             => esc_html__( 'Failed to copy shortcode. Please select and copy manually.', 'aicoso-click-to-chat' ),
				'select_products_text'   => esc_html__( 'Select products...', 'aicoso-click-to-chat' ),
				'select_categories_text' => esc_html__( 'Select categories...', 'aicoso-click-to-chat' ),
				'select_pages_text'      => esc_html__( 'Select pages...', 'aicoso-click-to-chat' ),
				'i18n'                   => array(
					'confirm_delete'    => esc_html__( 'Are you sure you want to delete this WhatsApp number?', 'aicoso-click-to-chat' ),
					'number_required'   => esc_html__( 'WhatsApp number is required.', 'aicoso-click-to-chat' ),
					'name_required'     => esc_html__( 'Name is required.', 'aicoso-click-to-chat' ),
					'select_products'   => esc_html__( 'Select products...', 'aicoso-click-to-chat' ),
					'select_categories' => esc_html__( 'Select categories...', 'aicoso-click-to-chat' ),
					'select_pages'      => esc_html__( 'Select pages...', 'aicoso-click-to-chat' ),
				),
			)
		);
	}

	/**
	 * Add action links to the plugins page.
	 *
	 * @param array $links Plugin action links.
	 * @return array Modified action links.
	 */
	public function ctc_chat_add_action_links( $links ) {
		$ctc_chat_plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=click-to-chat' ) . '">' . esc_html__( 'Settings', 'aicoso-click-to-chat' ) . '</a>',
		);

		return array_merge( $ctc_chat_plugin_links, $links );
	}

	/**
	 * Render the main settings page.
	 */
	public function ctc_chat_render_settings_page() {
		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Prevent duplicate rendering.
		if ( self::$ctc_chat_settings_page_rendered ) {
			return;
		}

		// Mark as rendered.
		self::$ctc_chat_settings_page_rendered = true;

		// Handle form submission.
		if ( isset( $_POST['ctc_chat_save_settings'] ) && check_admin_referer( 'ctc_chat_settings_nonce', 'ctc_chat_settings_nonce' ) ) {
			$this->ctc_chat_save_settings();
		}

		// Include the view file.
		include CTC_CHAT_PLUGIN_DIR . 'admin/views/settings-page.php';
	}

	/**
	 * Render the WhatsApp numbers page.
	 */
	public function ctc_chat_render_numbers_page() {
		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle form submission.
		if ( isset( $_POST['ctc_chat_save_numbers'] ) && check_admin_referer( 'ctc_chat_numbers_nonce', 'ctc_chat_numbers_nonce' ) ) {
			$this->ctc_chat_save_numbers();
		}

		// Include the view file.
		include CTC_CHAT_PLUGIN_DIR . 'admin/views/numbers-page.php';
	}

	/**
	 * Render the message templates page.
	 */
	public function ctc_chat_render_templates_page() {
		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle form submission.
		if ( isset( $_POST['ctc_chat_save_templates'] ) && check_admin_referer( 'ctc_chat_templates_nonce', 'ctc_chat_templates_nonce' ) ) {
			$this->ctc_chat_save_templates();
		}

		// Include the view file.
		include CTC_CHAT_PLUGIN_DIR . 'admin/views/templates-page.php';
	}

	/**
	 * Render the shortcode generator page.
	 */
	public function ctc_chat_render_shortcode_page() {
		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Include the view file.
		include CTC_CHAT_PLUGIN_DIR . 'admin/views/shortcode-page.php';
	}

	/**
	 * Save general settings.
	 */
	private function ctc_chat_save_settings() {
		// Get existing settings.
		$ctc_chat_settings = get_option( 'ctc_chat_settings', array() );

		// Nonce is verified in render_settings_page() before calling this method.
		// phpcs:disable WordPress.Security.NonceVerification.Missing

		// Get the current tab for redirect.
		$ctc_chat_current_tab = isset( $_POST['ctc_chat_current_tab'] ) ? sanitize_text_field( wp_unslash( $_POST['ctc_chat_current_tab'] ) ) : 'general';

		// Save plugin enabled status.
		$ctc_chat_settings['ctc_chat_plugin_enabled'] = isset( $_POST['ctc_chat_plugin_enabled'] ) ? true : false;

		// Sanitize and update button settings.
		if ( isset( $_POST['ctc_chat_button'] ) && is_array( $_POST['ctc_chat_button'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized individually below.
			$ctc_chat_button      = wp_unslash( $_POST['ctc_chat_button'] );
			$ctc_chat_button_settings = array(
				'ctc_chat_text'       => isset( $ctc_chat_button['ctc_chat_text'] ) ? sanitize_text_field( $ctc_chat_button['ctc_chat_text'] ) : '',
				'ctc_chat_icon'       => isset( $ctc_chat_button['ctc_chat_icon'] ) ? true : false,
				'ctc_chat_bg_color'   => isset( $ctc_chat_button['ctc_chat_bg_color'] ) ? sanitize_hex_color( $ctc_chat_button['ctc_chat_bg_color'] ) : '#25D366',
				'ctc_chat_text_color' => isset( $ctc_chat_button['ctc_chat_text_color'] ) ? sanitize_hex_color( $ctc_chat_button['ctc_chat_text_color'] ) : '#ffffff',
			);

			$ctc_chat_settings['ctc_chat_button_settings'] = $ctc_chat_button_settings;
		}

		// Sanitize and update single product settings.
		if ( isset( $_POST['ctc_chat_single_product'] ) && is_array( $_POST['ctc_chat_single_product'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized individually below.
			$ctc_chat_single_product = wp_unslash( $_POST['ctc_chat_single_product'] );
			$ctc_chat_single_product_settings     = array(
				'ctc_chat_enabled'  => isset( $_POST['ctc_chat_single_product']['ctc_chat_enabled'] ) ? true : false,
				'ctc_chat_position' => isset( $ctc_chat_single_product['ctc_chat_position'] ) ? sanitize_text_field( $ctc_chat_single_product['ctc_chat_position'] ) : 'after_add_to_cart',
			);

			$ctc_chat_settings['ctc_chat_single_product'] = $ctc_chat_single_product_settings;
		}

		// Sanitize and update shop page settings.
		if ( isset( $_POST['ctc_chat_shop_page'] ) && is_array( $_POST['ctc_chat_shop_page'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized individually below.
			$ctc_chat_shop_page = wp_unslash( $_POST['ctc_chat_shop_page'] );
			$ctc_chat_shop_page_settings     = array(
				'ctc_chat_enabled'  => isset( $_POST['ctc_chat_shop_page']['ctc_chat_enabled'] ) ? true : false,
				'ctc_chat_position' => isset( $ctc_chat_shop_page['ctc_chat_position'] ) ? sanitize_text_field( $ctc_chat_shop_page['ctc_chat_position'] ) : 'after_add_to_cart',
			);

			$ctc_chat_settings['ctc_chat_shop_page'] = $ctc_chat_shop_page_settings;
		}

		// Sanitize and update cart page settings.
		$ctc_chat_cart_page_position = isset( $_POST['ctc_chat_cart_page']['ctc_chat_position'] ) ? sanitize_text_field( wp_unslash( $_POST['ctc_chat_cart_page']['ctc_chat_position'] ) ) : 'after_cart_table';
		$ctc_chat_settings['ctc_chat_cart_page']  = array(
			'ctc_chat_enabled'  => isset( $_POST['ctc_chat_cart_page']['ctc_chat_enabled'] ) ? true : false,
			'ctc_chat_position' => $ctc_chat_cart_page_position,
		);

		// Sanitize and update checkout page settings.
		$ctc_chat_checkout_page_position = isset( $_POST['ctc_chat_checkout_page']['ctc_chat_position'] ) ? sanitize_text_field( wp_unslash( $_POST['ctc_chat_checkout_page']['ctc_chat_position'] ) ) : 'after_payment';
		$ctc_chat_settings['ctc_chat_checkout_page']  = array(
			'ctc_chat_enabled'  => isset( $_POST['ctc_chat_checkout_page']['ctc_chat_enabled'] ) ? true : false,
			'ctc_chat_position' => $ctc_chat_checkout_page_position,
		);

		// Sanitize and update thank you page settings.
		$ctc_chat_settings['ctc_chat_thankyou_page'] = array(
			'ctc_chat_enabled' => isset( $_POST['ctc_chat_thankyou_page']['ctc_chat_enabled'] ) ? true : false,
		);

		// Sanitize and update floating button settings.
		if ( isset( $_POST['ctc_chat_floating_button'] ) && is_array( $_POST['ctc_chat_floating_button'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized individually below.
			$ctc_chat_floating_button = wp_unslash( $_POST['ctc_chat_floating_button'] );
			$ctc_chat_floating_button_settings     = array(
				'ctc_chat_enabled'  => isset( $_POST['ctc_chat_floating_button']['ctc_chat_enabled'] ) ? true : false,
				'ctc_chat_position' => isset( $ctc_chat_floating_button['ctc_chat_position'] ) ? sanitize_text_field( $ctc_chat_floating_button['ctc_chat_position'] ) : 'bottom_right',
			);

			$ctc_chat_settings['ctc_chat_floating_button'] = $ctc_chat_floating_button_settings;
		}

		// Sanitize and update exclusions.
		// Always reset exclusions to ensure removed items are cleared.
		$ctc_chat_exclusions = array(
			'ctc_chat_pages'      => array(),
			'ctc_chat_posts'      => array(),
			'ctc_chat_categories' => array(),
			'ctc_chat_tags'       => array(),
			'ctc_chat_products'   => array(),
		);

		if ( isset( $_POST['ctc_chat_exclusions'] ) && is_array( $_POST['ctc_chat_exclusions'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized individually below.
			$ctc_chat_exclusions_data = wp_unslash( $_POST['ctc_chat_exclusions'] );

			// Pages.
			if ( isset( $ctc_chat_exclusions_data['ctc_chat_pages'] ) && is_array( $ctc_chat_exclusions_data['ctc_chat_pages'] ) ) {
				foreach ( $ctc_chat_exclusions_data['ctc_chat_pages'] as $page_id ) {
					$ctc_chat_exclusions['ctc_chat_pages'][] = absint( $page_id );
				}
			}

			// Posts.
			if ( isset( $ctc_chat_exclusions_data['ctc_chat_posts'] ) && is_array( $ctc_chat_exclusions_data['ctc_chat_posts'] ) ) {
				foreach ( $ctc_chat_exclusions_data['ctc_chat_posts'] as $post_id ) {
					$ctc_chat_exclusions['ctc_chat_posts'][] = absint( $post_id );
				}
			}

			// Categories.
			if ( isset( $ctc_chat_exclusions_data['ctc_chat_categories'] ) && is_array( $ctc_chat_exclusions_data['ctc_chat_categories'] ) ) {
				foreach ( $ctc_chat_exclusions_data['ctc_chat_categories'] as $category_id ) {
					$ctc_chat_exclusions['ctc_chat_categories'][] = absint( $category_id );
				}
			}

			// Tags.
			if ( isset( $ctc_chat_exclusions_data['ctc_chat_tags'] ) && is_array( $ctc_chat_exclusions_data['ctc_chat_tags'] ) ) {
				foreach ( $ctc_chat_exclusions_data['ctc_chat_tags'] as $tag_id ) {
					$ctc_chat_exclusions['ctc_chat_tags'][] = absint( $tag_id );
				}
			}

			// Products.
			if ( isset( $ctc_chat_exclusions_data['ctc_chat_products'] ) && is_array( $ctc_chat_exclusions_data['ctc_chat_products'] ) ) {
				foreach ( $ctc_chat_exclusions_data['ctc_chat_products'] as $product_id ) {
					$ctc_chat_exclusions['ctc_chat_products'][] = absint( $product_id );
				}
			}
		}

		$ctc_chat_settings['ctc_chat_exclusions'] = $ctc_chat_exclusions;

		// Sanitize and update advanced settings.
		$ctc_chat_catalog_mode = isset( $_POST['ctc_chat_advanced']['ctc_chat_catalog_mode'] ) ? true : false;

		// If catalog mode is enabled, force all hide options to be true.
		if ( $ctc_chat_catalog_mode ) {
			$ctc_chat_advanced = array(
				'ctc_chat_hide_add_to_cart'      => true,
				'ctc_chat_hide_proceed_checkout' => true,
				'ctc_chat_hide_place_order'      => true,
				'ctc_chat_catalog_mode'          => true,
			);
		} else {
			// Otherwise, check individual options.
			$ctc_chat_advanced = array(
				'ctc_chat_hide_add_to_cart'      => isset( $_POST['ctc_chat_advanced']['ctc_chat_hide_add_to_cart'] ) ? true : false,
				'ctc_chat_hide_proceed_checkout' => isset( $_POST['ctc_chat_advanced']['ctc_chat_hide_proceed_checkout'] ) ? true : false,
				'ctc_chat_hide_place_order'      => isset( $_POST['ctc_chat_advanced']['ctc_chat_hide_place_order'] ) ? true : false,
				'ctc_chat_catalog_mode'          => false,
			);
		}

		$ctc_chat_settings['ctc_chat_advanced'] = $ctc_chat_advanced;

		// Update settings.
		update_option( 'ctc_chat_settings', $ctc_chat_settings );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		// Store success message in transient (will be displayed after redirect).
		set_transient( 'ctc_chat_settings_message', 'success', 30 );

		// Redirect to the same tab.
		$redirect_url = add_query_arg(
			array(
				'page' => 'click-to-chat',
				'tab'  => $ctc_chat_current_tab,
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Save WhatsApp numbers.
	 */
	private function ctc_chat_save_numbers() {
		// Nonce is verified in render_numbers_page() before calling this method.
		// phpcs:disable WordPress.Security.NonceVerification.Missing

		// Get existing settings.
		$ctc_chat_settings = get_option( 'ctc_chat_settings', array() );

		// Initialize empty array for WhatsApp numbers.
		$ctc_chat_whatsapp_numbers = array();

		// Process deleted numbers.
		$ctc_chat_deleted_numbers = array();
		if ( isset( $_POST['ctc_chat_deleted_numbers'] ) && ! empty( $_POST['ctc_chat_deleted_numbers'] ) ) {
			$ctc_chat_deleted_numbers = array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['ctc_chat_deleted_numbers'] ) ) ) );
		}

		// Check for duplicate names.
		$ctc_chat_number_names  = array();
		$ctc_chat_has_duplicate = false;

		// Process submitted numbers.
		if ( isset( $_POST['ctc_numbers'] ) && is_array( $_POST['ctc_numbers'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized individually below.
			$ctc_chat_numbers = wp_unslash( $_POST['ctc_numbers'] );

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'CTC Chat: Processing numbers array: ' . print_r( $ctc_chat_numbers, true ) );
			}

			foreach ( $ctc_chat_numbers as $number_index => $number_data ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( 'CTC Chat: Processing number ' . $number_index . ': ' . print_r( $number_data, true ) );
				}
				// Get the number ID.
				$number_id = isset( $number_data['ctc_chat_id'] ) ? absint( $number_data['ctc_chat_id'] ) : 0;

				// Skip this number if it was deleted.
				if ( in_array( $number_id, $ctc_chat_deleted_numbers, true ) ) {
					continue;
				}

				// Check for duplicate names.
				$name = isset( $number_data['ctc_chat_name'] ) ? sanitize_text_field( $number_data['ctc_chat_name'] ) : '';
				if ( in_array( $name, $ctc_chat_number_names, true ) ) {
					// We found a duplicate name.
					$ctc_chat_has_duplicate = true;
					continue; // Skip this number.
				}

				// Add name to our tracking array.
				if ( ! empty( $name ) ) {
					$ctc_chat_number_names[] = $name;
				}

				$ctc_chat_assignments = array(
					'ctc_chat_products'   => array(),
					'ctc_chat_categories' => array(),
					'ctc_chat_pages'      => array(),
				);

				// Process product assignments.
				if ( isset( $number_data['ctc_chat_assignments']['ctc_chat_products'] ) && is_array( $number_data['ctc_chat_assignments']['ctc_chat_products'] ) ) {
					foreach ( $number_data['ctc_chat_assignments']['ctc_chat_products'] as $product_id ) {
						$ctc_chat_assignments['ctc_chat_products'][] = absint( $product_id );
					}
				}

				// Process category assignments.
				if ( isset( $number_data['ctc_chat_assignments']['ctc_chat_categories'] ) && is_array( $number_data['ctc_chat_assignments']['ctc_chat_categories'] ) ) {
					foreach ( $number_data['ctc_chat_assignments']['ctc_chat_categories'] as $category_id ) {
						$ctc_chat_assignments['ctc_chat_categories'][] = absint( $category_id );
					}
				}

				// Process page assignments.
				if ( isset( $number_data['ctc_chat_assignments']['ctc_chat_pages'] ) && is_array( $number_data['ctc_chat_assignments']['ctc_chat_pages'] ) ) {
					foreach ( $number_data['ctc_chat_assignments']['ctc_chat_pages'] as $page_id ) {
						$ctc_chat_assignments['ctc_chat_pages'][] = absint( $page_id );
					}
				}

				// Add sanitized number data to the array.
				$ctc_chat_whatsapp_numbers[] = array(
					'ctc_chat_id'          => isset( $number_data['ctc_chat_id'] ) ? absint( $number_data['ctc_chat_id'] ) : 0,
					'ctc_chat_name'        => isset( $number_data['ctc_chat_name'] ) ? sanitize_text_field( $number_data['ctc_chat_name'] ) : '',
					'ctc_chat_number'      => isset( $number_data['ctc_chat_number'] ) ? sanitize_text_field( $number_data['ctc_chat_number'] ) : '',
					'ctc_chat_description' => isset( $number_data['ctc_chat_description'] ) ? sanitize_textarea_field( $number_data['ctc_chat_description'] ) : '',
					'ctc_chat_is_default'  => ! empty( $number_data['ctc_chat_is_default'] ),
					'ctc_chat_assignments' => $ctc_chat_assignments,
				);
			}
		}

		// Update WhatsApp numbers in settings.
		$ctc_chat_settings['ctc_chat_whatsapp_numbers'] = $ctc_chat_whatsapp_numbers;

		// Update settings.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'CTC Chat: About to update settings with ' . count( $ctc_chat_whatsapp_numbers ) . ' numbers' );
		}

		update_option( 'ctc_chat_settings', $ctc_chat_settings );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'CTC Chat: Settings updated successfully' );
		}

		// Add message based on whether we found duplicates.
		if ( $ctc_chat_has_duplicate ) {
			add_settings_error(
				'ctc_numbers',
				'ctc_numbers_duplicates',
				esc_html__( 'Some WhatsApp numbers were not saved because they had duplicate names. Each WhatsApp number must have a unique name.', 'aicoso-click-to-chat' ),
				'error'
			);
		} else {
			add_settings_error(
				'ctc_numbers',
				'ctc_numbers_updated',
				esc_html__( 'WhatsApp numbers saved successfully.', 'aicoso-click-to-chat' ),
				'updated'
			);
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Save message templates.
	 */
	private function ctc_chat_save_templates() {
		// Nonce is verified in render_templates_page() before calling this method.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		// Get existing settings.
		$ctc_chat_settings = get_option( 'ctc_chat_settings', array() );

		// Process submitted templates.
		if ( isset( $_POST['ctc_chat_templates'] ) && is_array( $_POST['ctc_chat_templates'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized individually below.
			$ctc_chat_templates     = wp_unslash( $_POST['ctc_chat_templates'] );
			$ctc_chat_message_templates = array(
				'ctc_chat_single_product' => isset( $ctc_chat_templates['single_product'] ) ? sanitize_textarea_field( $ctc_chat_templates['single_product'] ) : '',
				'ctc_chat_cart_checkout'  => isset( $ctc_chat_templates['cart_checkout'] ) ? sanitize_textarea_field( $ctc_chat_templates['cart_checkout'] ) : '',
				'ctc_chat_thank_you'      => isset( $ctc_chat_templates['thank_you'] ) ? sanitize_textarea_field( $ctc_chat_templates['thank_you'] ) : '',
				'ctc_chat_floating'       => isset( $ctc_chat_templates['floating'] ) ? sanitize_textarea_field( $ctc_chat_templates['floating'] ) : '',
				'ctc_chat_variations'     => isset( $ctc_chat_templates['variations'] ) ? sanitize_textarea_field( $ctc_chat_templates['variations'] ) : '',
			);

			// Update message templates in settings.
			$ctc_chat_settings['ctc_chat_message_templates'] = $ctc_chat_message_templates;

			// Update settings.
			update_option( 'ctc_chat_settings', $ctc_chat_settings );

			// Add success message.
			add_settings_error(
				'ctc_templates',
				'ctc_templates_updated',
				esc_html__( 'Message templates saved successfully.', 'aicoso-click-to-chat' ),
				'updated'
			);
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Add meta boxes to product edit screen.
	 */
	public function ctc_chat_add_product_meta_boxes() {
		add_meta_box(
			'ctc_chat_product_settings',
			esc_html__( 'WhatsApp Shopping Settings', 'aicoso-click-to-chat' ),
			array( $this, 'render_product_meta_box' ),
			'product',
			'side',
			'default'
		);
	}

	/**
	 * Render meta box on product edit screen.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function ctc_chat_render_product_meta_box( $post ) {
		// Add nonce for security.
		wp_nonce_field( 'ctc_chat_product_meta_nonce', 'ctc_chat_product_meta_nonce' );

		// Get current values.
		$ctc_chat_hide_button     = get_post_meta( $post->ID, '_ctc_chat_hide_button', true );
		$ctc_chat_custom_message  = get_post_meta( $post->ID, '_ctc_chat_custom_message', true );
		$ctc_chat_assigned_number = get_post_meta( $post->ID, '_ctc_chat_assigned_number', true );

		// Get available WhatsApp numbers.
		$ctc_chat_whatsapp_numbers = isset( $this->ctc_chat_settings['ctc_chat_whatsapp_numbers'] ) ? $this->ctc_chat_settings['ctc_chat_whatsapp_numbers'] : array();

		// Output the meta box HTML.
		?>
		<p>
				<label>
				<input type="checkbox" name="ctc_chat_hide_button" value="1" <?php checked( $ctc_chat_hide_button, '1' ); ?> />
				<?php esc_html_e( 'Hide WhatsApp button on this product', 'aicoso-click-to-chat' ); ?>
			</label>
		</p>

		<p>
			<label for="ctc_assigned_number"><?php esc_html_e( 'Assign specific WhatsApp number:', 'aicoso-click-to-chat' ); ?></label>
			<select name="ctc_chat_assigned_number" id="ctc_chat_assigned_number">
				<option value=""><?php esc_html_e( 'Default (based on rules)', 'aicoso-click-to-chat' ); ?></option>
				<?php foreach ( $ctc_chat_whatsapp_numbers as $number ) : ?>
					<option value="<?php echo esc_attr( $number['ctc_chat_id'] ); ?>" <?php selected( $ctc_chat_assigned_number, $number['ctc_chat_id'] ); ?>>
						<?php echo esc_html( $number['ctc_chat_name'] . ' (' . $number['ctc_chat_number'] . ')' ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="ctc_custom_message"><?php esc_html_e( 'Custom message template (overrides default):', 'aicoso-click-to-chat' ); ?></label>
			<textarea name="ctc_chat_custom_message" id="ctc_chat_custom_message" rows="4" class="widefat"><?php echo esc_textarea( $ctc_chat_custom_message ); ?></textarea>
			<span class="description">
				<?php esc_html_e( 'Available placeholders: {product_name}, {price}, {product_url}', 'aicoso-click-to-chat' ); ?>
			</span>
		</p>
		<?php
	}

	/**
	 * Save product meta data.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object.
	 */
	public function ctc_chat_save_product_meta( $post_id, $post ) {
		// Check if nonce is valid.
		if ( ! isset( $_POST['ctc_chat_product_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ctc_chat_product_meta_nonce'] ) ), 'ctc_chat_product_meta_nonce' ) ) {
			return;
		}

		// Check if user has permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Update hide button setting.
		if ( isset( $_POST['ctc_chat_hide_button'] ) ) {
			update_post_meta( $post_id, '_ctc_chat_hide_button', '1' );
		} else {
			delete_post_meta( $post_id, '_ctc_chat_hide_button' );
		}

		// Update assigned number.
		if ( isset( $_POST['ctc_chat_assigned_number'] ) && ! empty( $_POST['ctc_chat_assigned_number'] ) ) {
			update_post_meta( $post_id, '_ctc_chat_assigned_number', sanitize_text_field( wp_unslash( $_POST['ctc_chat_assigned_number'] ) ) );
		} else {
			delete_post_meta( $post_id, '_ctc_chat_assigned_number' );
		}

		// Update custom message.
		if ( isset( $_POST['ctc_chat_custom_message'] ) && ! empty( $_POST['ctc_chat_custom_message'] ) ) {
			update_post_meta( $post_id, '_ctc_chat_custom_message', sanitize_textarea_field( wp_unslash( $_POST['ctc_chat_custom_message'] ) ) );
		} else {
			delete_post_meta( $post_id, '_ctc_chat_custom_message' );
		}
	}
}
