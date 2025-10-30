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
	private $settings;

	/**
	 * Flag to track if settings page has been rendered.
	 *
	 * @var bool
	 */
	private static $settings_page_rendered = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->settings = get_option( 'ctc_chat_settings', array() );

		// Initialize hooks.
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Add admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Register admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Add plugin action links.
		add_filter( 'plugin_action_links_' . CTC_CHAT_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );

		// Add meta box to product edit screen.
		add_action( 'add_meta_boxes', array( $this, 'add_product_meta_boxes' ) );

		// Save product meta.
		add_action( 'save_post_product', array( $this, 'save_product_meta' ), 10, 2 );
	}

	/**
	 * Add admin menu.
	 */
	public function add_admin_menu() {
		add_menu_page(
			esc_html__( 'Click to Chat', 'aicoso-click-to-chat' ),
			esc_html__( 'Click to Chat', 'aicoso-click-to-chat' ),
			'manage_options',
			'click-to-chat',
			array( $this, 'render_settings_page' ),
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
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'click-to-chat',
			esc_html__( 'WhatsApp Numbers', 'aicoso-click-to-chat' ),
			esc_html__( 'WhatsApp Numbers', 'aicoso-click-to-chat' ),
			'manage_options',
			'click-to-chat-numbers',
			array( $this, 'render_numbers_page' )
		);

		add_submenu_page(
			'click-to-chat',
			esc_html__( 'Message Templates', 'aicoso-click-to-chat' ),
			esc_html__( 'Message Templates', 'aicoso-click-to-chat' ),
			'manage_options',
			'click-to-chat-templates',
			array( $this, 'render_templates_page' )
		);

		add_submenu_page(
			'click-to-chat',
			esc_html__( 'Shortcode Generator', 'aicoso-click-to-chat' ),
			esc_html__( 'Shortcode Generator', 'aicoso-click-to-chat' ),
			'manage_options',
			'click-to-chat-shortcodes',
			array( $this, 'render_shortcode_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
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
	public function add_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=click-to-chat' ) . '">' . esc_html__( 'Settings', 'aicoso-click-to-chat' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Render the main settings page.
	 */
	public function render_settings_page() {
		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Prevent duplicate rendering.
		if ( self::$settings_page_rendered ) {
			return;
		}

		// Mark as rendered.
		self::$settings_page_rendered = true;

		// Handle form submission.
		if ( isset( $_POST['ctc_chat_save_settings'] ) && check_admin_referer( 'ctc_chat_settings_nonce', 'ctc_chat_settings_nonce' ) ) {
			$this->save_settings();
		}

		// Include the view file.
		include CTC_CHAT_PLUGIN_DIR . 'admin/views/settings-page.php';
	}

	/**
	 * Render the WhatsApp numbers page.
	 */
	public function render_numbers_page() {
		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle form submission.
		if ( isset( $_POST['ctc_chat_save_numbers'] ) && check_admin_referer( 'ctc_chat_numbers_nonce', 'ctc_chat_numbers_nonce' ) ) {
			$this->save_numbers();
		}

		// Include the view file.
		include CTC_CHAT_PLUGIN_DIR . 'admin/views/numbers-page.php';
	}

	/**
	 * Render the message templates page.
	 */
	public function render_templates_page() {
		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle form submission.
		if ( isset( $_POST['ctc_chat_save_templates'] ) && check_admin_referer( 'ctc_chat_templates_nonce', 'ctc_chat_templates_nonce' ) ) {
			$this->save_templates();
		}

		// Include the view file.
		include CTC_CHAT_PLUGIN_DIR . 'admin/views/templates-page.php';
	}

	/**
	 * Render the shortcode generator page.
	 */
	public function render_shortcode_page() {
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
	private function save_settings() {
		// Get existing settings.
		$settings = get_option( 'ctc_chat_settings', array() );

		// Nonce is verified in render_settings_page() before calling this method.
		// phpcs:disable WordPress.Security.NonceVerification.Missing

		// Get the current tab for redirect.
		$current_tab = isset( $_POST['ctc_chat_current_tab'] ) ? sanitize_text_field( wp_unslash( $_POST['ctc_chat_current_tab'] ) ) : 'general';

		// Save plugin enabled status.
		$settings['plugin_enabled'] = isset( $_POST['ctc_chat_plugin_enabled'] ) ? true : false;

		// Sanitize and update button settings.
		if ( isset( $_POST['ctc_chat_button'] ) && is_array( $_POST['ctc_chat_button'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized individually below.
			$ctc_button      = wp_unslash( $_POST['ctc_chat_button'] );
			$button_settings = array(
				'text'       => isset( $ctc_button['text'] ) ? sanitize_text_field( $ctc_button['text'] ) : '',
				'icon'       => isset( $_POST['ctc_chat_button']['icon'] ) ? true : false,
				'bg_color'   => isset( $ctc_button['bg_color'] ) ? sanitize_hex_color( $ctc_button['bg_color'] ) : '#25D366',
				'text_color' => isset( $ctc_button['text_color'] ) ? sanitize_hex_color( $ctc_button['text_color'] ) : '#ffffff',
				'custom_css' => isset( $ctc_button['custom_css'] ) ? sanitize_textarea_field( $ctc_button['custom_css'] ) : '',
			);

			$settings['button_settings'] = $button_settings;
		}

		// Sanitize and update single product settings.
		if ( isset( $_POST['ctc_chat_single_product'] ) && is_array( $_POST['ctc_chat_single_product'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized individually below.
			$ctc_single_product = wp_unslash( $_POST['ctc_chat_single_product'] );
			$single_product     = array(
				'enabled'  => isset( $_POST['ctc_chat_single_product']['enabled'] ) ? true : false,
				'position' => isset( $ctc_single_product['position'] ) ? sanitize_text_field( $ctc_single_product['position'] ) : 'after_add_to_cart',
			);

			$settings['single_product'] = $single_product;
		}

		// Sanitize and update shop page settings.
		if ( isset( $_POST['ctc_chat_shop_page'] ) && is_array( $_POST['ctc_chat_shop_page'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized individually below.
			$ctc_shop_page = wp_unslash( $_POST['ctc_chat_shop_page'] );
			$shop_page     = array(
				'enabled'  => isset( $_POST['ctc_chat_shop_page']['enabled'] ) ? true : false,
				'position' => isset( $ctc_shop_page['position'] ) ? sanitize_text_field( $ctc_shop_page['position'] ) : 'after_add_to_cart',
			);

			$settings['shop_page'] = $shop_page;
		}

		// Sanitize and update cart page settings.
		$ctc_cart_page_position = isset( $_POST['ctc_chat_cart_page']['position'] ) ? sanitize_text_field( wp_unslash( $_POST['ctc_chat_cart_page']['position'] ) ) : 'after_cart_table';
		$settings['cart_page']  = array(
			'enabled'  => isset( $_POST['ctc_chat_cart_page']['enabled'] ) ? true : false,
			'position' => $ctc_cart_page_position,
		);

		// Sanitize and update checkout page settings.
		$ctc_checkout_page_position = isset( $_POST['ctc_chat_checkout_page']['position'] ) ? sanitize_text_field( wp_unslash( $_POST['ctc_chat_checkout_page']['position'] ) ) : 'after_payment';
		$settings['checkout_page']  = array(
			'enabled'  => isset( $_POST['ctc_chat_checkout_page']['enabled'] ) ? true : false,
			'position' => $ctc_checkout_page_position,
		);

		// Sanitize and update thank you page settings.
		$settings['thankyou_page'] = array(
			'enabled' => isset( $_POST['ctc_chat_thankyou_page']['enabled'] ) ? true : false,
		);

		// Sanitize and update floating button settings.
		if ( isset( $_POST['ctc_chat_floating_button'] ) && is_array( $_POST['ctc_chat_floating_button'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized individually below.
			$ctc_floating_button = wp_unslash( $_POST['ctc_chat_floating_button'] );
			$floating_button     = array(
				'enabled'  => isset( $_POST['ctc_chat_floating_button']['enabled'] ) ? true : false,
				'position' => isset( $ctc_floating_button['position'] ) ? sanitize_text_field( $ctc_floating_button['position'] ) : 'bottom_right',
			);

			$settings['floating_button'] = $floating_button;
		}

		// Sanitize and update exclusions.
		// Always reset exclusions to ensure removed items are cleared.
		$exclusions = array(
			'pages'      => array(),
			'posts'      => array(),
			'categories' => array(),
			'tags'       => array(),
			'products'   => array(),
		);

		if ( isset( $_POST['ctc_chat_exclusions'] ) && is_array( $_POST['ctc_chat_exclusions'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized individually below.
			$ctc_exclusions = wp_unslash( $_POST['ctc_chat_exclusions'] );

			// Pages.
			if ( isset( $ctc_exclusions['pages'] ) && is_array( $ctc_exclusions['pages'] ) ) {
				foreach ( $ctc_exclusions['pages'] as $page_id ) {
					$exclusions['pages'][] = absint( $page_id );
				}
			}

			// Posts.
			if ( isset( $ctc_exclusions['posts'] ) && is_array( $ctc_exclusions['posts'] ) ) {
				foreach ( $ctc_exclusions['posts'] as $post_id ) {
					$exclusions['posts'][] = absint( $post_id );
				}
			}

			// Categories.
			if ( isset( $ctc_exclusions['categories'] ) && is_array( $ctc_exclusions['categories'] ) ) {
				foreach ( $ctc_exclusions['categories'] as $category_id ) {
					$exclusions['categories'][] = absint( $category_id );
				}
			}

			// Tags.
			if ( isset( $ctc_exclusions['tags'] ) && is_array( $ctc_exclusions['tags'] ) ) {
				foreach ( $ctc_exclusions['tags'] as $tag_id ) {
					$exclusions['tags'][] = absint( $tag_id );
				}
			}

			// Products.
			if ( isset( $ctc_exclusions['products'] ) && is_array( $ctc_exclusions['products'] ) ) {
				foreach ( $ctc_exclusions['products'] as $product_id ) {
					$exclusions['products'][] = absint( $product_id );
				}
			}
		}

		$settings['exclusions'] = $exclusions;

		// Sanitize and update advanced settings.
		$catalog_mode = isset( $_POST['ctc_chat_advanced']['catalog_mode'] ) ? true : false;

		// If catalog mode is enabled, force all hide options to be true.
		if ( $catalog_mode ) {
			$advanced = array(
				'hide_add_to_cart'      => true,
				'hide_proceed_checkout' => true,
				'hide_place_order'      => true,
				'catalog_mode'          => true,
			);
		} else {
			// Otherwise, check individual options.
			$advanced = array(
				'hide_add_to_cart'      => isset( $_POST['ctc_chat_advanced']['hide_add_to_cart'] ) ? true : false,
				'hide_proceed_checkout' => isset( $_POST['ctc_chat_advanced']['hide_proceed_checkout'] ) ? true : false,
				'hide_place_order'      => isset( $_POST['ctc_chat_advanced']['hide_place_order'] ) ? true : false,
				'catalog_mode'          => false,
			);
		}

		$settings['advanced'] = $advanced;

		// Update settings.
		update_option( 'ctc_chat_settings', $settings );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		// Store success message in transient (will be displayed after redirect).
		set_transient( 'ctc_chat_settings_message', 'success', 30 );

		// Redirect to the same tab.
		$redirect_url = add_query_arg(
			array(
				'page' => 'click-to-chat',
				'tab'  => $current_tab,
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Save WhatsApp numbers.
	 */
	private function save_numbers() {
		// Nonce is verified in render_numbers_page() before calling this method.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		// Get existing settings.
		$settings = get_option( 'ctc_chat_settings', array() );

		// Initialize empty array for WhatsApp numbers.
		$whatsapp_numbers = array();

		// Process deleted numbers.
		$deleted_numbers = array();
		if ( isset( $_POST['ctc_chat_deleted_numbers'] ) && ! empty( $_POST['ctc_chat_deleted_numbers'] ) ) {
			$deleted_numbers = array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['ctc_chat_deleted_numbers'] ) ) ) );
		}

		// Check for duplicate names.
		$number_names  = array();
		$has_duplicate = false;

		// Process submitted numbers.
		if ( isset( $_POST['ctc_numbers'] ) && is_array( $_POST['ctc_numbers'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized individually below.
			$ctc_numbers = wp_unslash( $_POST['ctc_numbers'] );
			foreach ( $ctc_numbers as $number_data ) {
				// Get the number ID.
				$number_id = isset( $number_data['id'] ) ? absint( $number_data['id'] ) : 0;

				// Skip this number if it was deleted.
				if ( in_array( $number_id, $deleted_numbers, true ) ) {
					continue;
				}

				// Check for duplicate names.
				$name = isset( $number_data['name'] ) ? sanitize_text_field( $number_data['name'] ) : '';
				if ( in_array( $name, $number_names, true ) ) {
					// We found a duplicate name.
					$has_duplicate = true;
					continue; // Skip this number.
				}

				// Add name to our tracking array.
				if ( ! empty( $name ) ) {
					$number_names[] = $name;
				}

				$assignments = array(
					'products'   => array(),
					'categories' => array(),
					'pages'      => array(),
				);

				// Process product assignments.
				if ( isset( $number_data['assignments']['products'] ) && is_array( $number_data['assignments']['products'] ) ) {
					foreach ( $number_data['assignments']['products'] as $product_id ) {
						$assignments['products'][] = absint( $product_id );
					}
				}

				// Process category assignments.
				if ( isset( $number_data['assignments']['categories'] ) && is_array( $number_data['assignments']['categories'] ) ) {
					foreach ( $number_data['assignments']['categories'] as $category_id ) {
						$assignments['categories'][] = absint( $category_id );
					}
				}

				// Process page assignments.
				if ( isset( $number_data['assignments']['pages'] ) && is_array( $number_data['assignments']['pages'] ) ) {
					foreach ( $number_data['assignments']['pages'] as $page_id ) {
						$assignments['pages'][] = absint( $page_id );
					}
				}

				// Add sanitized number data to the array.
				$whatsapp_numbers[] = array(
					'id'          => isset( $number_data['id'] ) ? absint( $number_data['id'] ) : 0,
					'name'        => isset( $number_data['name'] ) ? sanitize_text_field( $number_data['name'] ) : '',
					'number'      => isset( $number_data['number'] ) ? sanitize_text_field( $number_data['number'] ) : '',
					'description' => isset( $number_data['description'] ) ? sanitize_textarea_field( $number_data['description'] ) : '',
					'is_default'  => ! empty( $number_data['is_default'] ),
					'assignments' => $assignments,
				);
			}
		}

		// Update WhatsApp numbers in settings.
		$settings['whatsapp_numbers'] = $whatsapp_numbers;

		// Update settings.
		update_option( 'ctc_chat_settings', $settings );

		// Add message based on whether we found duplicates.
		if ( $has_duplicate ) {
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
	private function save_templates() {
		// Nonce is verified in render_templates_page() before calling this method.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		// Get existing settings.
		$settings = get_option( 'ctc_chat_settings', array() );

		// Process submitted templates.
		if ( isset( $_POST['ctc_chat_templates'] ) && is_array( $_POST['ctc_chat_templates'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each field is sanitized individually below.
			$ctc_templates     = wp_unslash( $_POST['ctc_chat_templates'] );
			$message_templates = array(
				'single_product' => isset( $ctc_templates['single_product'] ) ? sanitize_textarea_field( $ctc_templates['single_product'] ) : '',
				'cart_checkout'  => isset( $ctc_templates['cart_checkout'] ) ? sanitize_textarea_field( $ctc_templates['cart_checkout'] ) : '',
				'thank_you'      => isset( $ctc_templates['thank_you'] ) ? sanitize_textarea_field( $ctc_templates['thank_you'] ) : '',
				'floating'       => isset( $ctc_templates['floating'] ) ? sanitize_textarea_field( $ctc_templates['floating'] ) : '',
				'variations'     => isset( $ctc_templates['variations'] ) ? sanitize_textarea_field( $ctc_templates['variations'] ) : '',
			);

			// Update message templates in settings.
			$settings['message_templates'] = $message_templates;

			// Update settings.
			update_option( 'ctc_chat_settings', $settings );

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
	public function add_product_meta_boxes() {
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
	public function render_product_meta_box( $post ) {
		// Add nonce for security.
		wp_nonce_field( 'ctc_chat_product_meta_nonce', 'ctc_chat_product_meta_nonce' );

		// Get current values.
		$hide_button     = get_post_meta( $post->ID, '_ctc_chat_hide_button', true );
		$custom_message  = get_post_meta( $post->ID, '_ctc_chat_custom_message', true );
		$assigned_number = get_post_meta( $post->ID, '_ctc_chat_assigned_number', true );

		// Get available WhatsApp numbers.
		$whatsapp_numbers = isset( $this->settings['whatsapp_numbers'] ) ? $this->settings['whatsapp_numbers'] : array();

		// Output the meta box HTML.
		?>
		<p>
				<label>
				<input type="checkbox" name="ctc_chat_hide_button" value="1" <?php checked( $hide_button, '1' ); ?> />
				<?php esc_html_e( 'Hide WhatsApp button on this product', 'aicoso-click-to-chat' ); ?>
			</label>
		</p>

		<p>
			<label for="ctc_assigned_number"><?php esc_html_e( 'Assign specific WhatsApp number:', 'aicoso-click-to-chat' ); ?></label>
			<select name="ctc_chat_assigned_number" id="ctc_chat_assigned_number">
				<option value=""><?php esc_html_e( 'Default (based on rules)', 'aicoso-click-to-chat' ); ?></option>
				<?php foreach ( $whatsapp_numbers as $number ) : ?>
					<option value="<?php echo esc_attr( $number['id'] ); ?>" <?php selected( $assigned_number, $number['id'] ); ?>>
						<?php echo esc_html( $number['name'] . ' (' . $number['number'] . ')' ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="ctc_custom_message"><?php esc_html_e( 'Custom message template (overrides default):', 'aicoso-click-to-chat' ); ?></label>
			<textarea name="ctc_chat_custom_message" id="ctc_chat_custom_message" rows="4" class="widefat"><?php echo esc_textarea( $custom_message ); ?></textarea>
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
	public function save_product_meta( $post_id, $post ) {
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
