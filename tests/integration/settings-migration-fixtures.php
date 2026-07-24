<?php
/**
 * WP-CLI-compatible settings migration fixture matrix.
 *
 * @package ClickToChat
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'CTC_Chat_Settings_Migrator' ) ) {
	require_once dirname( __DIR__, 2 ) . '/includes/class-ctc-chat-settings-migrator.php';
}

$ctc_fixture_failures = array();
$ctc_fixture_assert = static function ( $condition, $message ) use ( &$ctc_fixture_failures ) {
	if ( ! $condition ) {
		$ctc_fixture_failures[] = $message;
	}
};
$ctc_fixture_snapshot = array(
	'settings' => get_option( 'ctc_chat_settings', false ),
	'version'  => get_option( 'ctc_chat_settings_schema_version', false ),
	'error'    => get_option( 'ctc_chat_settings_migration_error', false ),
);
$ctc_fixture_restore = static function () use ( $ctc_fixture_snapshot ) {
	foreach ( array( 'settings', 'version', 'error' ) as $key ) {
		$option = 'settings' === $key ? 'ctc_chat_settings' : ( 'version' === $key ? 'ctc_chat_settings_schema_version' : 'ctc_chat_settings_migration_error' );
		if ( false === $ctc_fixture_snapshot[ $key ] ) {
			delete_option( $option );
		} else {
			update_option( $option, $ctc_fixture_snapshot[ $key ] );
		}
	}
};

try {
	$legacy = array(
		'ctc_chat_plugin_enabled'    => false,
		'ctc_chat_whatsapp_numbers'  => array(
			array(
				'ctc_chat_id'          => 7,
				'ctc_chat_name'        => 'Sales',
				'ctc_chat_number'      => '+910000000007',
				'ctc_chat_description' => 'Primary sales desk',
				'ctc_chat_is_default'  => true,
				'ctc_chat_assignments' => array(
					'ctc_chat_products'   => array( 10, 11 ),
					'ctc_chat_categories' => array( 20 ),
					'ctc_chat_pages'      => array( 30, 31 ),
				),
				'extension_number_flag' => null,
			),
			array(
				'ctc_chat_id'          => 8,
				'ctc_chat_name'        => 'Support',
				'ctc_chat_number'      => '+910000000008',
				'ctc_chat_description' => '',
				'ctc_chat_is_default'  => false,
				'ctc_chat_assignments' => array(
					'ctc_chat_products'   => array(),
					'ctc_chat_categories' => array( 21, 22 ),
					'ctc_chat_pages'      => array(),
				),
			),
		),
		'ctc_chat_button_settings'   => array(
			'ctc_chat_text'       => 'Legacy button',
			'ctc_chat_icon'       => false,
			'ctc_chat_bg_color'   => '#112233',
			'ctc_chat_text_color' => '#fefefe',
			'extension_flag'      => 'retain-me',
		),
		'ctc_chat_single_product'    => array(
			'ctc_chat_enabled'  => false,
			'ctc_chat_position' => 'before_add_to_cart',
		),
		'ctc_chat_shop_page'         => array(
			'ctc_chat_enabled'  => true,
			'ctc_chat_position' => 'after_add_to_cart',
		),
		'ctc_chat_cart_page'         => array(
			'ctc_chat_enabled'  => true,
			'ctc_chat_position' => 'before_cart_table',
		),
		'ctc_chat_checkout_page'     => array(
			'ctc_chat_enabled'  => true,
			'ctc_chat_position' => 'before_payment',
		),
		'ctc_chat_thankyou_page'     => array(
			'ctc_chat_enabled' => true,
		),
		'ctc_chat_floating_button'   => array(
			'ctc_chat_enabled'  => true,
			'ctc_chat_position' => 'bottom_left',
		),
		'ctc_chat_message_templates' => array(
			'ctc_chat_single_product' => "Product line 1\nProduct line 2",
			'ctc_chat_cart_checkout'  => 'Cart {cart_total}',
			'ctc_chat_thank_you'      => 'Order {order_number}',
			'ctc_chat_floating'       => 'Floating {current_page_url}',
			'ctc_chat_variations'     => 'Variation {variation_details}',
		),
		'ctc_chat_exclusions'        => array(
			'ctc_chat_pages'      => array( 101, 102 ),
			'ctc_chat_posts'      => array( 201 ),
			'ctc_chat_categories' => array( 301, 302 ),
			'ctc_chat_tags'       => array( 401 ),
			'ctc_chat_products'   => array( 501, 502 ),
		),
		'ctc_chat_advanced'          => array(
			'ctc_chat_hide_add_to_cart'      => true,
			'ctc_chat_hide_proceed_checkout' => false,
			'ctc_chat_hide_place_order'      => true,
			'ctc_chat_catalog_mode'           => false,
		),
		'extension_top_level'        => array( 'value' => 'unchanged' ),
	);
	update_option( 'ctc_chat_settings', $legacy );
	delete_option( 'ctc_chat_settings_schema_version' );
	delete_option( 'ctc_chat_settings_migration_error' );
	CTC_Chat_Settings_Migrator::migrate();
	$actual = get_option( 'ctc_chat_settings', array() );
	$ctc_fixture_assert( false === $actual['plugin_enabled'], 'Legacy false value was overwritten.' );
	$ctc_fixture_assert( 'Legacy button' === $actual['button_settings']['text'], 'Button text was not mapped.' );
	$ctc_fixture_assert( false === $actual['button_settings']['icon'], 'Button icon setting was not mapped.' );
	$ctc_fixture_assert( '#112233' === $actual['button_settings']['bg_color'], 'Button background was not mapped.' );
	$ctc_fixture_assert( '#fefefe' === $actual['button_settings']['text_color'], 'Button text color was not mapped.' );
	$ctc_fixture_assert( 'retain-me' === $actual['ctc_chat_button_settings']['extension_flag'], 'Unknown container shell was not retained.' );
	$ctc_fixture_assert( 2 === count( $actual['whatsapp_numbers'] ), 'WhatsApp number list length changed.' );
	$ctc_fixture_assert( 7 === $actual['whatsapp_numbers'][0]['id'], 'Number ID was not mapped.' );
	$ctc_fixture_assert( 'Sales' === $actual['whatsapp_numbers'][0]['name'], 'Number name was not mapped.' );
	$ctc_fixture_assert( '+910000000007' === $actual['whatsapp_numbers'][0]['number'], 'Phone number was not mapped.' );
	$ctc_fixture_assert( 'Primary sales desk' === $actual['whatsapp_numbers'][0]['description'], 'Number description was not mapped.' );
	$ctc_fixture_assert( true === $actual['whatsapp_numbers'][0]['is_default'], 'Default-number selection was not mapped.' );
	$ctc_fixture_assert( array( 10, 11 ) === $actual['whatsapp_numbers'][0]['assignments']['products'], 'Product assignments were not mapped.' );
	$ctc_fixture_assert( array( 20 ) === $actual['whatsapp_numbers'][0]['assignments']['categories'], 'Category assignments were not mapped.' );
	$ctc_fixture_assert( array( 30, 31 ) === $actual['whatsapp_numbers'][0]['assignments']['pages'], 'Page assignments were not mapped.' );
	$ctc_fixture_assert( 8 === $actual['whatsapp_numbers'][1]['id'], 'Number order was not preserved.' );
	$ctc_fixture_assert( false === $actual['whatsapp_numbers'][1]['is_default'], 'False default-number value changed.' );
	$ctc_fixture_assert( array() === $actual['whatsapp_numbers'][1]['assignments']['products'], 'Empty assignment list changed.' );
	$ctc_fixture_assert( null === $actual['ctc_chat_whatsapp_numbers'][0]['extension_number_flag'], 'Unknown number field path changed.' );
	$ctc_fixture_assert( false === $actual['single_product']['enabled'], 'Single-product enabled value changed.' );
	$ctc_fixture_assert( 'before_add_to_cart' === $actual['single_product']['position'], 'Single-product position changed.' );
	$ctc_fixture_assert( true === $actual['shop_page']['enabled'], 'Shop placement was not mapped.' );
	$ctc_fixture_assert( 'before_cart_table' === $actual['cart_page']['position'], 'Cart placement was not mapped.' );
	$ctc_fixture_assert( 'before_payment' === $actual['checkout_page']['position'], 'Checkout placement was not mapped.' );
	$ctc_fixture_assert( true === $actual['thankyou_page']['enabled'], 'Thank-you placement was not mapped.' );
	$ctc_fixture_assert( 'bottom_left' === $actual['floating_button']['position'], 'Floating position was not mapped.' );
	$ctc_fixture_assert( "Product line 1\nProduct line 2" === $actual['message_templates']['single_product'], 'Multiline template changed.' );
	$ctc_fixture_assert( 'Cart {cart_total}' === $actual['message_templates']['cart_checkout'], 'Cart template changed.' );
	$ctc_fixture_assert( array( 101, 102 ) === $actual['exclusions']['pages'], 'Page exclusions were not mapped.' );
	$ctc_fixture_assert( array( 201 ) === $actual['exclusions']['posts'], 'Post exclusions were not mapped.' );
	$ctc_fixture_assert( array( 301, 302 ) === $actual['exclusions']['categories'], 'Category exclusions were not mapped.' );
	$ctc_fixture_assert( array( 401 ) === $actual['exclusions']['tags'], 'Tag exclusions were not mapped.' );
	$ctc_fixture_assert( array( 501, 502 ) === $actual['exclusions']['products'], 'Product exclusions were not mapped.' );
	$ctc_fixture_assert( true === $actual['advanced']['hide_add_to_cart'], 'Advanced add-to-cart setting was not mapped.' );
	$ctc_fixture_assert( false === $actual['advanced']['hide_proceed_checkout'], 'Advanced checkout setting changed.' );
	$ctc_fixture_assert( true === $actual['advanced']['hide_place_order'], 'Advanced place-order setting was not mapped.' );
	$ctc_fixture_assert( false === $actual['advanced']['catalog_mode'], 'Catalog-mode setting changed.' );
	$ctc_fixture_assert( array( 'value' => 'unchanged' ) === $actual['extension_top_level'], 'Unknown top-level data changed.' );
	$ctc_fixture_assert( '1.0.0' === get_option( 'ctc_chat_settings_schema_version' ), 'Schema marker was not advanced.' );
	$ctc_fixture_assert( ! array_key_exists( 'ctc_chat_plugin_enabled', $actual ), 'Recognized scalar legacy key remains.' );

	// Version 1.0.1 activation could add empty canonical defaults beside populated legacy data.
	$activation_mixed = array_merge( $legacy, ctc_chat_get_default_settings() );
	update_option( 'ctc_chat_settings', $activation_mixed );
	delete_option( 'ctc_chat_settings_schema_version' );
	CTC_Chat_Settings_Migrator::migrate();
	$actual = get_option( 'ctc_chat_settings', array() );
	$ctc_fixture_assert( 2 === count( $actual['whatsapp_numbers'] ), 'Activation-created mixed schema lost WhatsApp numbers.' );
	$ctc_fixture_assert( array( 501, 502 ) === $actual['exclusions']['products'], 'Activation-created mixed schema lost exclusions.' );
	// Repair the intermediate shape written by the first faulty list migration.
	$intermediate = ctc_chat_get_default_settings();
	$intermediate['ctc_chat_whatsapp_numbers'] = array(
		array(
			'id'          => 7,
			'name'        => 'Sales',
			'number'      => '+910000000007',
			'description' => 'Primary sales desk',
			'is_default'  => true,
			'assignments' => array(
				'products'   => array( 10, 11 ),
				'categories' => array( 20 ),
				'pages'      => array( 30, 31 ),
			),
		),
	);
	update_option( 'ctc_chat_settings', $intermediate );
	update_option( 'ctc_chat_settings_schema_version', CTC_Chat_Settings_Migrator::TARGET_SCHEMA );
	CTC_Chat_Settings_Migrator::migrate();
	$actual = get_option( 'ctc_chat_settings', array() );
	$ctc_fixture_assert( 1 === count( $actual['whatsapp_numbers'] ), 'Intermediate migration state did not restore its number record.' );
	$ctc_fixture_assert( '+910000000007' === $actual['whatsapp_numbers'][0]['number'], 'Intermediate migration state did not restore the phone value.' );
	$ctc_fixture_assert( array( 10, 11 ) === $actual['whatsapp_numbers'][0]['assignments']['products'], 'Intermediate migration state did not restore assignments.' );
	$ctc_fixture_assert( ! array_key_exists( 'ctc_chat_whatsapp_numbers', $actual ), 'Recovered legacy number shell was not pruned.' );
	$canonical = get_option( 'ctc_chat_settings', array() );
	$canonical['button_settings']['text'] = null;
	update_option( 'ctc_chat_settings', $canonical );
	CTC_Chat_Settings_Migrator::migrate();
	$actual = get_option( 'ctc_chat_settings', array() );
	$ctc_fixture_assert( array_key_exists( 'text', $actual['button_settings'] ) && null === $actual['button_settings']['text'], 'Canonical null was defaulted.' );

	$partial = get_option( 'ctc_chat_settings', array() );
	$partial['analytics']['enabled'] = false;
	$partial['analytics']['retention_days'] = null;
	update_option( 'ctc_chat_settings', $partial );
	CTC_Chat_Settings_Migrator::migrate();
	$actual = get_option( 'ctc_chat_settings', array() );
	$ctc_fixture_assert( false === $actual['analytics']['enabled'], 'Analytics false was overwritten.' );
	$ctc_fixture_assert( array_key_exists( 'retention_days', $actual['analytics'] ) && null === $actual['analytics']['retention_days'], 'Analytics null was defaulted.' );

	$mixed = array(
		'plugin_enabled' => false,
		'ctc_chat_plugin_enabled' => true,
		'button_settings' => array('text' => null),
		'ctc_chat_button_settings' => array('ctc_chat_text' => 'stale', 'extension_flag' => 'keep'),
	);
	update_option( 'ctc_chat_settings', $mixed );
	CTC_Chat_Settings_Migrator::migrate();
	$first = get_option( 'ctc_chat_settings', array() );
	CTC_Chat_Settings_Migrator::migrate();
	$second = get_option( 'ctc_chat_settings', array() );
	$ctc_fixture_assert( false === $first['plugin_enabled'], 'Canonical mixed-schema value lost.' );
	$ctc_fixture_assert( null === $first['button_settings']['text'], 'Canonical mixed null lost.' );
	$ctc_fixture_assert( 'keep' === $first['ctc_chat_button_settings']['extension_flag'], 'Mixed unknown shell lost.' );
	$ctc_fixture_assert( serialize( $first ) === serialize( $second ), 'Repeated migration drifted.' );
	$malformed = 'not-an-array';
	update_option( 'ctc_chat_settings', $malformed );
	delete_option( 'ctc_chat_settings_schema_version' );
	CTC_Chat_Settings_Migrator::migrate();
	$ctc_fixture_assert( $malformed === get_option( 'ctc_chat_settings' ), 'Malformed settings were replaced.' );
	$ctc_fixture_assert( 'malformed_settings' === get_option( 'ctc_chat_settings_migration_error' )['code'], 'Malformed diagnostic code missing.' );
} finally {
	$ctc_fixture_restore();
}

if ( empty( $ctc_fixture_failures ) ) {
	echo "settings-migration-fixtures: PASS\n";
} else {
	echo "settings-migration-fixtures: FAIL\n";
	foreach ( $ctc_fixture_failures as $failure ) {
		echo "- {$failure}\n";
	}
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::error( 'Fixture failures detected.' );
	}
}
