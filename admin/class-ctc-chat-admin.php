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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_menu_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Add plugin action links.
		add_filter( 'plugin_action_links_' . CTC_CHAT_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );

		// Add meta box to product edit screen.
		add_action( 'add_meta_boxes', array( $this, 'add_product_meta_boxes' ) );

		// Save product meta.
		add_action( 'save_post_product', array( $this, 'save_product_meta' ), 10, 2 );

		// Redirect legacy admin routes to the unified settings hub.
		add_action( 'admin_init', array( $this, 'maybe_redirect_legacy_admin_pages' ) );
	}

	/**
	 * Add admin menu.
	 */
	/**
	 * Get the main Aicoso logo URL for admin headers.
	 *
	 * @return string
	 */
	public function get_logo_url() {
		return CTC_CHAT_PLUGIN_URL . 'admin/images/aicoso-main-logo.svg';
	}

	/**
	 * Get the admin menu icon URL.
	 *
	 * @return string
	 */
	public function get_menu_icon_url() {
		return CTC_CHAT_PLUGIN_URL . 'admin/images/aicoso-admin-menu-logo.svg';
	}

	/**
	 * Valid settings hub section slugs.
	 *
	 * @return string[]
	 */
	public function get_settings_section_slugs() {
		return array( 'general', 'numbers', 'templates', 'shortcodes' );
	}

	/**
	 * Resolve the active settings hub section from the request.
	 *
	 * @return string
	 */
	public function get_current_settings_section() {
		$valid_sections = $this->get_settings_section_slugs();
		$section        = 'general';

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Section selection is display-only.
		if ( isset( $_GET['ctc-settings-tab'] ) ) {
			$section = sanitize_text_field( wp_unslash( $_GET['ctc-settings-tab'] ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return in_array( $section, $valid_sections, true ) ? $section : 'general';
	}

	/**
	 * Build a settings hub admin URL.
	 *
	 * @param string $section Settings section slug.
	 * @param string $tab     Optional configuration sub-tab slug.
	 * @return string
	 */
	public function get_settings_hub_url( $section = 'general', $tab = '' ) {
		$args = array(
			'page' => 'click-to-chat-settings',
		);

		if ( 'general' !== $section ) {
			$args['ctc-settings-tab'] = $section;
		}

		if ( '' !== $tab ) {
			$args['ctc-tab'] = $tab;
		}

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Dashicon slugs for settings screens.
	 *
	 * @param string $key Icon key.
	 * @return string
	 */
	public function get_settings_icon( $key ) {
		$icons = array(
			// Header tabs.
			'tab_configuration'       => 'dashicons-admin-tools',
			'tab_numbers'             => 'dashicons-smartphone',
			'tab_templates'           => 'dashicons-format-chat',
			'tab_shortcodes'          => 'dashicons-shortcode',
			// Configuration split nav.
			'config_general'          => 'dashicons-admin-settings',
			'config_button'           => 'dashicons-admin-appearance',
			'config_display'          => 'dashicons-layout',
			'config_exclusions'       => 'dashicons-hidden',
			// Message template cards.
			'template_product'        => 'dashicons-products',
			'template_shop'           => 'dashicons-store',
			'template_variations'     => 'dashicons-randomize',
			'template_cart'           => 'dashicons-cart',
			'template_thankyou'       => 'dashicons-saved',
			'template_floating'       => 'dashicons-sticky',
			'preview'                 => 'dashicons-visibility',
			// WhatsApp numbers.
			'number'                  => 'dashicons-smartphone',
			'assignments'             => 'dashicons-networking',
			'assign_products'         => 'dashicons-products',
			'assign_categories'       => 'dashicons-category',
			'assign_pages'            => 'dashicons-admin-page',
			// Shortcode generator.
			'shortcode_panel'         => 'dashicons-shortcode',
			'shortcode_type_product'  => 'dashicons-products',
			'shortcode_type_cart'     => 'dashicons-cart',
			'shortcode_type_shop'     => 'dashicons-store',
			'shortcode_type_general'  => 'dashicons-format-chat',
			'shortcode_output'        => 'dashicons-editor-code',
			'shortcode_copy'          => 'dashicons-clipboard',
			'shortcode_examples'      => 'dashicons-lightbulb',
			'shortcode_usage'         => 'dashicons-info',
			// Reports tabs.
			'report_clicks'           => 'dashicons-list-view',
			'report_placements'       => 'dashicons-layout',
			'report_products'         => 'dashicons-products',
			'report_numbers'          => 'dashicons-smartphone',
			'report_pages'            => 'dashicons-admin-page',
		);

		return isset( $icons[ $key ] ) ? $icons[ $key ] : 'dashicons-admin-generic';
	}

	/**
	 * Get settings hub header tab definitions.
	 *
	 * @return array
	 */
	public function get_settings_header_tabs() {
		return array(
			array(
				'id'    => 'general',
				'label' => esc_html__( 'Configuration', 'aicoso-click-to-chat' ),
				'url'   => $this->get_settings_hub_url( 'general' ),
				'icon'  => $this->get_settings_icon( 'tab_configuration' ),
			),
			array(
				'id'    => 'numbers',
				'label' => esc_html__( 'WhatsApp Numbers', 'aicoso-click-to-chat' ),
				'url'   => $this->get_settings_hub_url( 'numbers' ),
				'icon'  => $this->get_settings_icon( 'tab_numbers' ),
			),
			array(
				'id'    => 'templates',
				'label' => esc_html__( 'Message Templates', 'aicoso-click-to-chat' ),
				'url'   => $this->get_settings_hub_url( 'templates' ),
				'icon'  => $this->get_settings_icon( 'tab_templates' ),
			),
			array(
				'id'    => 'shortcodes',
				'label' => esc_html__( 'Shortcode Generator', 'aicoso-click-to-chat' ),
				'url'   => $this->get_settings_hub_url( 'shortcodes' ),
				'icon'  => $this->get_settings_icon( 'tab_shortcodes' ),
			),
		);
	}

	/**
	 * Valid report tab slugs.
	 *
	 * @return string[]
	 */
	public function get_report_slugs() {
		return array( 'clicks', 'placements', 'products', 'numbers', 'pages' );
	}

	/**
	 * Resolve the active report tab from the request.
	 *
	 * @return string
	 */
	public function get_current_report() {
		$valid_reports = $this->get_report_slugs();
		$report        = 'clicks';

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Report selection is display-only.
		if ( isset( $_GET['report'] ) ) {
			$candidate = sanitize_key( wp_unslash( $_GET['report'] ) );
			if ( in_array( $candidate, $valid_reports, true ) ) {
				$report = $candidate;
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return $report;
	}

	/**
	 * Build a Reports screen URL.
	 *
	 * @param string $report Report slug.
	 * @param array  $args   Optional extra query args.
	 * @return string
	 */
	public function get_reports_url( $report = 'clicks', $args = array() ) {
		$report = sanitize_key( $report );
		if ( ! in_array( $report, $this->get_report_slugs(), true ) ) {
			$report = 'clicks';
		}

		$url_args = array_merge(
			array(
				'page'   => 'click-to-chat-reports',
				'report' => $report,
			),
			$args
		);

		return add_query_arg( $url_args, admin_url( 'admin.php' ) );
	}

	/**
	 * Get Reports header tab definitions.
	 *
	 * @return array
	 */
	public function get_reports_header_tabs() {
		return array(
			array(
				'id'    => 'clicks',
				'label' => esc_html__( 'Click Log', 'aicoso-click-to-chat' ),
				'url'   => $this->get_reports_url( 'clicks' ),
				'icon'  => $this->get_settings_icon( 'report_clicks' ),
			),
			array(
				'id'    => 'placements',
				'label' => esc_html__( 'By Placement', 'aicoso-click-to-chat' ),
				'url'   => $this->get_reports_url( 'placements' ),
				'icon'  => $this->get_settings_icon( 'report_placements' ),
			),
			array(
				'id'    => 'products',
				'label' => esc_html__( 'By Product', 'aicoso-click-to-chat' ),
				'url'   => $this->get_reports_url( 'products' ),
				'icon'  => $this->get_settings_icon( 'report_products' ),
			),
			array(
				'id'    => 'numbers',
				'label' => esc_html__( 'By WhatsApp Number', 'aicoso-click-to-chat' ),
				'url'   => $this->get_reports_url( 'numbers' ),
				'icon'  => $this->get_settings_icon( 'report_numbers' ),
			),
			array(
				'id'    => 'pages',
				'label' => esc_html__( 'By Page', 'aicoso-click-to-chat' ),
				'url'   => $this->get_reports_url( 'pages' ),
				'icon'  => $this->get_settings_icon( 'report_pages' ),
			),
		);
	}

	/**
	 * Render the left navigation for configuration sub-sections.
	 *
	 * @param string $current_tab Active configuration tab slug.
	 * @return void
	 */
	public function render_settings_split_nav( $current_tab ) {
		$tabs = array(
			array(
				'id'    => 'general',
				'label' => esc_html__( 'General', 'aicoso-click-to-chat' ),
				'url'   => $this->get_settings_hub_url( 'general', 'general' ),
				'icon'  => $this->get_settings_icon( 'config_general' ),
			),
			array(
				'id'    => 'button',
				'label' => esc_html__( 'Button Style', 'aicoso-click-to-chat' ),
				'url'   => $this->get_settings_hub_url( 'general', 'button' ),
				'icon'  => $this->get_settings_icon( 'config_button' ),
			),
			array(
				'id'    => 'display',
				'label' => esc_html__( 'Display', 'aicoso-click-to-chat' ),
				'url'   => $this->get_settings_hub_url( 'general', 'display' ),
				'icon'  => $this->get_settings_icon( 'config_display' ),
			),
			array(
				'id'    => 'exclusions',
				'label' => esc_html__( 'Exclusions', 'aicoso-click-to-chat' ),
				'url'   => $this->get_settings_hub_url( 'general', 'exclusions' ),
				'icon'  => $this->get_settings_icon( 'config_exclusions' ),
			),
		);

		echo '<ul class="ctc-settings-split-nav">';
		foreach ( $tabs as $tab ) {
			$is_active = ( $current_tab === $tab['id'] );
			$classes   = 'ctc-settings-split-nav__item' . ( $is_active ? ' is-active' : '' );
			echo '<li class="' . esc_attr( $classes ) . '">';
			echo '<a href="' . esc_url( $tab['url'] ) . '"' . ( $is_active ? ' aria-current="page"' : '' ) . '>';
			if ( '' !== $tab['icon'] ) {
				echo '<span class="dashicons ' . esc_attr( $tab['icon'] ) . '" aria-hidden="true"></span>';
			}
			echo '<span class="ctc-settings-split-nav__label">' . esc_html( $tab['label'] ) . '</span>';
			echo '</a>';
			echo '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Open a settings section card.
	 *
	 * @param string $title   Card title.
	 * @param array  $args    Optional card arguments.
	 * @return void
	 */
	public function render_settings_card_open( $title, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'description' => '',
				'icon'        => '',
				'class'       => '',
			)
		);
		?>
		<section class="ctc-settings-card <?php echo esc_attr( $args['class'] ); ?>">
			<header class="ctc-settings-card__header">
				<?php if ( '' !== $args['icon'] ) : ?>
					<span class="ctc-settings-card__icon dashicons <?php echo esc_attr( $args['icon'] ); ?>" aria-hidden="true"></span>
				<?php endif; ?>
				<div class="ctc-settings-card__heading">
					<h3 class="ctc-settings-card__title"><?php echo esc_html( $title ); ?></h3>
					<?php if ( '' !== $args['description'] ) : ?>
						<p class="ctc-settings-card__description"><?php echo esc_html( $args['description'] ); ?></p>
					<?php endif; ?>
				</div>
			</header>
			<div class="ctc-settings-card__body">
		<?php
	}

	/**
	 * Close a settings section card.
	 *
	 * @return void
	 */
	public function render_settings_card_close() {
		echo '</div></section>';
	}

	/**
	 * Render a unified settings save footer.
	 *
	 * @param string $submit_name  Submit button name attribute.
	 * @param string $submit_label Submit button label.
	 * @return void
	 */
	public function render_settings_footer( $submit_name, $submit_label ) {
		?>
		<div class="ctc-settings-footer">
			<button type="submit" name="<?php echo esc_attr( $submit_name ); ?>" class="button button-primary">
				<?php echo esc_html( $submit_label ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Redirect legacy admin routes to the unified settings hub.
	 *
	 * @return void
	 */
	public function maybe_redirect_legacy_admin_pages() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Legacy deep links are display-only redirects.
		if ( ! is_admin() || ! isset( $_GET['page'] ) ) {
			return;
		}

		$page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
		$args = array();

		switch ( $page ) {
			case 'click-to-chat-numbers':
				$args = array(
					'page'             => 'click-to-chat-settings',
					'ctc-settings-tab' => 'numbers',
				);
				break;
			case 'click-to-chat-templates':
				$args = array(
					'page'             => 'click-to-chat-settings',
					'ctc-settings-tab' => 'templates',
				);
				break;
			case 'click-to-chat-shortcodes':
				$args = array(
					'page'             => 'click-to-chat-settings',
					'ctc-settings-tab' => 'shortcodes',
				);
				break;
		}

		if ( empty( $args ) && 'click-to-chat' === $page && ( isset( $_GET['ctc-tab'] ) || isset( $_GET['tab'] ) ) ) {
			$tab  = isset( $_GET['ctc-tab'] ) ? sanitize_text_field( wp_unslash( $_GET['ctc-tab'] ) ) : sanitize_text_field( wp_unslash( $_GET['tab'] ) );
			$args = array(
				'page'             => 'click-to-chat-settings',
				'ctc-settings-tab' => 'general',
				'ctc-tab'          => $tab,
			);
		}

		if ( ! empty( $args ) ) {
			wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
			exit;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Render the shared Aicoso product header.
	 *
	 * @param array $args Header arguments.
	 * @return void
	 */
	public function render_product_header( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'logo_url'              => $this->get_logo_url(),
				'brand_label'           => esc_html__( 'AICOSO Click to Chat', 'aicoso-click-to-chat' ),
				'breadcrumb_root'       => esc_html__( 'Click to Chat', 'aicoso-click-to-chat' ),
				'breadcrumb_parent'     => '',
				'breadcrumb_parent_url' => '',
				'breadcrumb_current'    => '',
				'actions'               => array(),
				'has_header_tabs'       => false,
			)
		);
		$header_class = $args['has_header_tabs'] ? 'ctc-product-header--with-tabs' : 'ctc-product-header--standalone';
		?>
		<div class="ctc-product-header <?php echo esc_attr( $header_class ); ?>">
			<div class="ctc-product-brand">
				<img class="ctc-product-brand__logo" src="<?php echo esc_url( $args['logo_url'] ); ?>" alt="<?php echo esc_attr( $args['brand_label'] ); ?>" />
				<div class="ctc-product-brand__copy">
					<h1><?php echo esc_html( $args['brand_label'] ); ?></h1>
					<nav class="ctc-product-breadcrumb" aria-label="<?php esc_attr_e( 'Click to Chat breadcrumb', 'aicoso-click-to-chat' ); ?>">
						<ol>
							<li><?php echo esc_html( $args['breadcrumb_root'] ); ?></li>
							<?php if ( '' !== $args['breadcrumb_parent'] ) : ?>
								<li>
									<?php if ( '' !== $args['breadcrumb_parent_url'] ) : ?>
										<a href="<?php echo esc_url( $args['breadcrumb_parent_url'] ); ?>"><?php echo esc_html( $args['breadcrumb_parent'] ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $args['breadcrumb_parent'] ); ?>
									<?php endif; ?>
								</li>
							<?php endif; ?>
							<?php if ( '' !== $args['breadcrumb_current'] ) : ?>
								<li aria-current="page"><?php echo esc_html( $args['breadcrumb_current'] ); ?></li>
							<?php endif; ?>
						</ol>
					</nav>
				</div>
			</div>
			<?php if ( ! empty( $args['actions'] ) && is_array( $args['actions'] ) ) : ?>
				<div class="ctc-product-header__actions">
					<?php foreach ( $args['actions'] as $action ) : ?>
						<?php
						$action = wp_parse_args(
							$action,
							array(
								'url'   => '',
								'label' => '',
								'class' => 'button',
							)
						);
						if ( '' === $action['url'] || '' === $action['label'] ) {
							continue;
						}
						?>
						<a class="<?php echo esc_attr( $action['class'] ); ?>" href="<?php echo esc_url( $action['url'] ); ?>"><?php echo esc_html( $action['label'] ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render shared horizontal admin tabs.
	 *
	 * @param array  $tabs    Tab item data.
	 * @param string $current Current tab identifier.
	 * @param array  $args    Renderer arguments.
	 * @return void
	 */
	public function render_admin_tabs( $tabs, $current, $args = array() ) {
		if ( empty( $tabs ) || ! is_array( $tabs ) ) {
			return;
		}

		$args = wp_parse_args(
			$args,
			array(
				'aria_label' => esc_html__( 'Click to Chat navigation', 'aicoso-click-to-chat' ),
				'class'      => '',
			)
		);

		$classes = trim( 'subsubsub ctc_tablinks ctc-admin-tabs ' . $args['class'] );
		?>
		<ul class="<?php echo esc_attr( $classes ); ?>" aria-label="<?php echo esc_attr( $args['aria_label'] ); ?>">
			<?php foreach ( $tabs as $tab ) : ?>
				<?php
				$tab = wp_parse_args(
					$tab,
					array(
						'id'     => '',
						'label'  => '',
						'url'    => '',
						'icon'   => '',
						'active' => null,
					)
				);

				$is_active    = null === $tab['active'] ? $current === $tab['id'] : (bool) $tab['active'];
				$item_class   = $is_active ? 'ctc-active' : '';
				$aria_current = $is_active ? 'page' : '';
				?>
				<li>
					<?php if ( '' !== $tab['url'] ) : ?>
						<a class="<?php echo esc_attr( $item_class ); ?>" href="<?php echo esc_url( $tab['url'] ); ?>"<?php echo $aria_current ? ' aria-current="' . esc_attr( $aria_current ) . '"' : ''; ?>>
							<?php if ( '' !== $tab['icon'] ) : ?>
								<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>" aria-hidden="true"></span>
							<?php endif; ?>
							<?php echo esc_html( $tab['label'] ); ?>
						</a>
					<?php else : ?>
						<span class="<?php echo esc_attr( trim( $item_class . ' ctc-tab-current' ) ); ?>"<?php echo $aria_current ? ' aria-current="' . esc_attr( $aria_current ) . '"' : ''; ?>>
							<?php if ( '' !== $tab['icon'] ) : ?>
								<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>" aria-hidden="true"></span>
							<?php endif; ?>
							<?php echo esc_html( $tab['label'] ); ?>
						</span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Valid settings sub-tab slugs.
	 *
	 * @return string[]
	 */
	public function get_settings_tab_slugs() {
		return array( 'general', 'button', 'display', 'exclusions' );
	}

	/**
	 * Resolve the active settings sub-tab from the request.
	 *
	 * @return string
	 */
	public function get_current_settings_tab() {
		$valid_tabs = $this->get_settings_tab_slugs();
		$tab        = 'general';

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Tab selection is display-only.
		if ( isset( $_GET['ctc-tab'] ) ) {
			$tab = sanitize_text_field( wp_unslash( $_GET['ctc-tab'] ) );
		} elseif ( isset( $_GET['tab'] ) ) {
			// Legacy query parameter kept for backward compatibility.
			$tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return in_array( $tab, $valid_tabs, true ) ? $tab : 'general';
	}

	/**
	 * Render setup warnings when the plugin is not ready.
	 *
	 * @return void
	 */
	private function render_setup_status_notices() {
		$plugin_enabled = isset( $this->settings['plugin_enabled'] ) ? $this->settings['plugin_enabled'] : true;
		$has_valid_numbers = false;

		if ( isset( $this->settings['whatsapp_numbers'] ) && ! empty( $this->settings['whatsapp_numbers'] ) ) {
			foreach ( $this->settings['whatsapp_numbers'] as $number ) {
				if ( ! empty( $number['number'] ) ) {
					$has_valid_numbers = true;
					break;
				}
			}
		}

		if ( ! $plugin_enabled ) {
			echo '<div class="notice notice-warning inline ctc-setup-notice"><p><span class="dashicons dashicons-warning" aria-hidden="true"></span> ';
			esc_html_e( 'Click to Chat is disabled. WhatsApp buttons will not appear until you enable the plugin in Settings.', 'aicoso-click-to-chat' );
			echo '</p></div>';
		}

		if ( ! $has_valid_numbers ) {
			echo '<div class="notice notice-warning inline ctc-setup-notice"><p><span class="dashicons dashicons-warning" aria-hidden="true"></span> ';
			printf(
				/* translators: %s: link to Numbers page */
				esc_html__( 'Add at least one WhatsApp number before buttons can work. %s', 'aicoso-click-to-chat' ),
				'<a href="' . esc_url( $this->get_settings_hub_url( 'numbers' ) ) . '">' . esc_html__( 'Manage numbers', 'aicoso-click-to-chat' ) . '</a>'
			);
			echo '</p></div>';
		}
	}

	/**
	 * Render a plugin-native admin banner.
	 *
	 * @param string $message     Banner message.
	 * @param string $type        Banner type: info, success, warning, or error.
	 * @param bool   $dismissible Whether the banner can be dismissed.
	 * @return void
	 */
	public function render_admin_banner( $message, $type = 'info', $dismissible = false ) {
		$icons = array(
			'info'    => 'dashicons-info-outline',
			'success' => 'dashicons-yes-alt',
			'warning' => 'dashicons-warning',
			'error'   => 'dashicons-dismiss',
		);

		if ( ! isset( $icons[ $type ] ) ) {
			$type = 'info';
		}

		$role = in_array( $type, array( 'warning', 'error' ), true ) ? 'alert' : 'status';
		?>
		<div class="ctc-admin-banner ctc-admin-banner--<?php echo esc_attr( $type ); ?>" role="<?php echo esc_attr( $role ); ?>">
			<span class="ctc-admin-banner__icon dashicons <?php echo esc_attr( $icons[ $type ] ); ?>" aria-hidden="true"></span>
			<p class="ctc-admin-banner__message"><?php echo wp_kses_post( $message ); ?></p>
			<?php if ( $dismissible ) : ?>
				<button type="button" class="ctc-admin-banner__dismiss" aria-label="<?php esc_attr_e( 'Dismiss notification', 'aicoso-click-to-chat' ); ?>">
					<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
				</button>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render messages registered through the Settings API.
	 *
	 * @param string $setting Settings error group.
	 * @return void
	 */
	public function render_settings_messages( $setting ) {
		foreach ( get_settings_errors( $setting ) as $message ) {
			$type = isset( $message['type'] ) ? sanitize_key( $message['type'] ) : 'info';
			if ( in_array( $type, array( 'updated', 'success' ), true ) ) {
				$type = 'success';
			} elseif ( ! in_array( $type, array( 'info', 'warning', 'error' ), true ) ) {
				$type = 'info';
			}

			$this->render_admin_banner( $message['message'], $type, true );
		}
	}

	/**
	 * Build breadcrumb args for a settings sub-page.
	 *
	 * @param string $section   Settings section slug.
	 * @param string $config_tab Optional configuration sub-tab slug.
	 * @return array
	 */
	private function get_settings_breadcrumb_args( $section, $config_tab = 'general' ) {
		$config_tab_labels = array(
			'general'    => esc_html__( 'General', 'aicoso-click-to-chat' ),
			'button'     => esc_html__( 'Button Style', 'aicoso-click-to-chat' ),
			'display'    => esc_html__( 'Display', 'aicoso-click-to-chat' ),
			'exclusions' => esc_html__( 'Exclusions', 'aicoso-click-to-chat' ),
		);

		$args = array(
			'breadcrumb_parent'     => esc_html__( 'Settings', 'aicoso-click-to-chat' ),
			'breadcrumb_parent_url' => $this->get_settings_hub_url( 'general' ),
		);

		if ( 'general' === $section ) {
			$args['breadcrumb_current'] = isset( $config_tab_labels[ $config_tab ] ) ? $config_tab_labels[ $config_tab ] : $config_tab_labels['general'];
		}

		return $args;
	}

	/**
	 * Open the shared admin shell.
	 *
	 * @param array $args Shell arguments.
	 * @return void
	 */
	private function render_admin_shell_open( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'breadcrumb_parent'     => '',
				'breadcrumb_parent_url' => '',
				'breadcrumb_current'    => '',
				'intro'                 => '',
				'show_setup_notices'    => true,
				'header_tabs'           => array(),
				'header_tab_current'    => '',
			)
		);
		?>
		<div class="wrap ctc-wrap">
			<?php
			$this->render_product_header(
				array(
					'breadcrumb_parent'     => $args['breadcrumb_parent'],
					'breadcrumb_parent_url' => $args['breadcrumb_parent_url'],
					'breadcrumb_current'    => $args['breadcrumb_current'],
					'has_header_tabs'       => ! empty( $args['header_tabs'] ),
				)
			);

			if ( ! empty( $args['header_tabs'] ) ) {
				$tab_aria = esc_html__( 'Settings sections', 'aicoso-click-to-chat' );
				if ( esc_html__( 'Reports', 'aicoso-click-to-chat' ) === $args['breadcrumb_current'] ) {
					$tab_aria = esc_html__( 'Report views', 'aicoso-click-to-chat' );
				}

				$this->render_admin_tabs(
					$args['header_tabs'],
					$args['header_tab_current'],
					array(
						'aria_label' => $tab_aria,
						'class'      => 'ctc-header-tabs',
					)
				);
			}
			?>

			<?php if ( $args['show_setup_notices'] ) : ?>
				<div class="ctc-admin-notices">
					<?php $this->render_setup_status_notices(); ?>
				</div>
			<?php endif; ?>

			<div class="ctc-native-screen">
				<?php if ( '' !== $args['intro'] ) : ?>
					<p class="ctc-page-intro"><?php echo esc_html( $args['intro'] ); ?></p>
				<?php endif; ?>
		<?php
	}

	/**
	 * Close the shared admin shell.
	 *
	 * @return void
	 */
	private function render_admin_shell_close() {
		?>
			</div>
		</div>
		<?php
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
			array( $this, 'render_dashboard_page' ),
			$this->get_menu_icon_url(),
			58 // Position after WooCommerce.
		);

		add_submenu_page(
			'click-to-chat',
			esc_html__( 'Dashboard', 'aicoso-click-to-chat' ),
			esc_html__( 'Dashboard', 'aicoso-click-to-chat' ),
			'manage_options',
			'click-to-chat',
			array( $this, 'render_dashboard_page' )
		);

		add_submenu_page(
			'click-to-chat',
			esc_html__( 'Reports', 'aicoso-click-to-chat' ),
			esc_html__( 'Reports', 'aicoso-click-to-chat' ),
			'manage_options',
			'click-to-chat-reports',
			array( $this, 'render_reports_page' )
		);

		add_submenu_page(
			'click-to-chat',
			esc_html__( 'Settings', 'aicoso-click-to-chat' ),
			esc_html__( 'Settings', 'aicoso-click-to-chat' ),
			'manage_options',
			'click-to-chat-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue sidebar menu styles on every admin screen.
	 *
	 * @return void
	 */
	public function enqueue_admin_menu_assets() {
		wp_enqueue_style(
			'ctc-chat-admin-menu',
			CTC_CHAT_PLUGIN_URL . 'admin/css/admin-menu.css',
			array(),
			CTC_CHAT_VERSION
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

		$this->enqueue_analytics_assets( $hook );
	}

	/**
	 * Enqueue dashboard and reports analytics assets.
	 *
	 * @param string $hook Admin page hook.
	 */
	private function enqueue_analytics_assets( $hook ) {
		$analytics_hooks = array(
			'toplevel_page_click-to-chat',
			'click-to-chat_page_click-to-chat-reports',
		);

		if ( ! in_array( $hook, $analytics_hooks, true ) ) {
			return;
		}

		$currency = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$';

		wp_enqueue_script(
			'ctc-chat-analytics-shared',
			CTC_CHAT_PLUGIN_URL . 'admin/js/analytics-shared.js',
			array( 'jquery' ),
			CTC_CHAT_VERSION,
			true
		);

		wp_localize_script(
			'ctc-chat-analytics-shared',
			'ctc_chat_analytics',
			array(
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ctc_chat_admin_nonce' ),
				'currency' => $currency,
				'i18n'     => array(
					'loading'    => __( 'Loading…', 'aicoso-click-to-chat' ),
					'error'      => __( 'Could not load analytics data.', 'aicoso-click-to-chat' ),
					'empty'       => __( 'No WhatsApp clicks in this period.', 'aicoso-click-to-chat' ),
					'unavailable' => __( 'Data unavailable', 'aicoso-click-to-chat' ),
					'disabled'           => __( 'Click tracking is disabled in Settings.', 'aicoso-click-to-chat' ),
					'clicks'             => __( 'clicks', 'aicoso-click-to-chat' ),
					'about'              => __( 'About', 'aicoso-click-to-chat' ),
					'all_numbers'        => __( 'All numbers', 'aicoso-click-to-chat' ),
					'unattributed'       => __( 'Unattributed', 'aicoso-click-to-chat' ),
					'number_filter'      => __( 'WhatsApp number', 'aicoso-click-to-chat' ),
					'filter_unavailable' => __( 'The selected WhatsApp number is no longer available. Showing all numbers.', 'aicoso-click-to-chat' ),
					'filter_retry_error' => __( 'The dashboard could not refresh after changing the number filter. Please try again.', 'aicoso-click-to-chat' ),
					'kpi_help'           => array(
						'whatsapp_clicks'    => __( 'Total WhatsApp button clicks recorded during the selected period. Repeated clicks are included.', 'aicoso-click-to-chat' ),
						'unique_clicks'      => __( 'Clicks counted once per visitor, placement, product, and order within the 24-hour deduplication window.', 'aicoso-click-to-chat' ),
						'high_intent_clicks' => __( 'WhatsApp clicks from Cart, Checkout, and Thank You pages during the selected period.', 'aicoso-click-to-chat' ),
						'cart_value_clicked' => __( 'Sum of cart totals captured when visitors clicked WhatsApp. This is not revenue or an average.', 'aicoso-click-to-chat' ),
						'mobile_share'       => __( 'Percentage of clicks from mobile devices among clicks with a recognized device type. When device data is unavailable, this metric is unavailable.', 'aicoso-click-to-chat' ),
						'top_placement'      => __( 'The button placement with the most WhatsApp clicks during the selected period.', 'aicoso-click-to-chat' ),
					),
				),
				'urls'     => array(
					'settings' => admin_url( 'admin.php?page=click-to-chat-settings' ),
					'reports'  => admin_url( 'admin.php?page=click-to-chat-reports' ),
					'numbers'  => $this->get_settings_hub_url( 'numbers' ),
				),
			)
		);

		if ( 'toplevel_page_click-to-chat' === $hook ) {
			wp_enqueue_script(
				'ctc-chat-analytics-dashboard',
				CTC_CHAT_PLUGIN_URL . 'admin/js/analytics-dashboard.js',
				array( 'ctc-chat-analytics-shared' ),
				CTC_CHAT_VERSION,
				true
			);
		}

		if ( 'click-to-chat_page_click-to-chat-reports' === $hook ) {
			wp_enqueue_script(
				'ctc-chat-analytics-reports',
				CTC_CHAT_PLUGIN_URL . 'admin/js/analytics-reports.js',
				array( 'ctc-chat-analytics-shared' ),
				CTC_CHAT_VERSION,
				true
			);
		}
	}

	/**
	 * Add action links to the plugins page.
	 *
	 * @param array $links Plugin action links.
	 * @return array Modified action links.
	 */
	public function add_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . esc_url( admin_url( 'admin.php?page=click-to-chat-settings' ) ) . '">' . esc_html__( 'Settings', 'aicoso-click-to-chat' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Render the dashboard page.
	 */
	public function render_dashboard_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->render_admin_shell_open(
			array(
				'breadcrumb_current' => esc_html__( 'Dashboard', 'aicoso-click-to-chat' ),
				'show_setup_notices' => false,
			)
		);

		include CTC_CHAT_PLUGIN_DIR . 'admin/views/dashboard-page.php';

		$this->render_admin_shell_close();
	}

	/**
	 * Render the reports page.
	 */
	public function render_reports_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_report = $this->get_current_report();

		$this->render_admin_shell_open(
			array(
				'breadcrumb_current' => esc_html__( 'Reports', 'aicoso-click-to-chat' ),
				'intro'              => esc_html__( 'Drill into WhatsApp click data by placement, product, number, and page.', 'aicoso-click-to-chat' ),
				'show_setup_notices' => false,
				'header_tabs'        => $this->get_reports_header_tabs(),
				'header_tab_current' => $current_report,
			)
		);

		include CTC_CHAT_PLUGIN_DIR . 'admin/views/reports-page.php';

		$this->render_admin_shell_close();
	}

	/**
	 * Render the unified settings hub.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( self::$settings_page_rendered ) {
			return;
		}

		self::$settings_page_rendered = true;

		$current_section = $this->get_current_settings_section();
		$current_tab     = $this->get_current_settings_tab();

		if ( isset( $_POST['ctc_chat_save_settings'] ) && check_admin_referer( 'ctc_chat_settings_nonce', 'ctc_chat_settings_nonce' ) ) {
			$this->save_settings();
		}

		if ( isset( $_POST['ctc_chat_save_numbers'] ) && check_admin_referer( 'ctc_chat_numbers_nonce', 'ctc_chat_numbers_nonce' ) ) {
			$this->save_numbers();
		}

		if ( isset( $_POST['ctc_chat_save_templates'] ) && check_admin_referer( 'ctc_chat_templates_nonce', 'ctc_chat_templates_nonce' ) ) {
			$this->save_templates();
		}

		$section_intros = array(
			'general'    => esc_html__( 'Configure WhatsApp buttons, placement, exclusions, and catalog mode.', 'aicoso-click-to-chat' ),
			'numbers'    => esc_html__( 'Add WhatsApp numbers and assign them to products, categories, or pages.', 'aicoso-click-to-chat' ),
			'templates'  => esc_html__( 'Customize pre-filled WhatsApp messages for products, cart, checkout, and floating buttons.', 'aicoso-click-to-chat' ),
			'shortcodes' => esc_html__( 'Build a custom WhatsApp button shortcode and copy it into any page or post.', 'aicoso-click-to-chat' ),
		);

		$breadcrumb_args = $this->get_settings_breadcrumb_args( $current_section, $current_tab );

		$this->render_admin_shell_open(
			array_merge(
				$breadcrumb_args,
				array(
					'intro'              => $section_intros[ $current_section ],
					'header_tabs'        => $this->get_settings_header_tabs(),
					'header_tab_current' => $current_section,
				)
			)
		);

		include CTC_CHAT_PLUGIN_DIR . 'admin/views/settings-hub-page.php';

		$this->render_admin_shell_close();
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

		// Sanitize and update cart/checkout abandonment nudge settings.
		if ( isset( $_POST['ctc_chat_cart_checkout_nudge'] ) && is_array( $_POST['ctc_chat_cart_checkout_nudge'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Fields sanitized individually below.
			$nudge_post = wp_unslash( $_POST['ctc_chat_cart_checkout_nudge'] );
			$settings['cart_checkout_nudge'] = array(
				'enabled'     => isset( $nudge_post['enabled'] ) ? true : false,
				'trigger'     => isset( $nudge_post['trigger'] ) && in_array( $nudge_post['trigger'], array( 'inactivity', 'exit_intent', 'both' ), true ) ? $nudge_post['trigger'] : 'both',
				'delay'       => isset( $nudge_post['delay'] ) ? max( 3, min( 300, absint( $nudge_post['delay'] ) ) ) : 20,
				'title'       => isset( $nudge_post['title'] ) ? sanitize_text_field( $nudge_post['title'] ) : esc_html__( 'Need help with your order?', 'aicoso-click-to-chat' ),
				'message'     => isset( $nudge_post['message'] ) ? sanitize_textarea_field( $nudge_post['message'] ) : esc_html__( 'Have questions about payment, shipping, or need assistance? Chat with us on WhatsApp!', 'aicoso-click-to-chat' ),
				'button_text' => isset( $nudge_post['button_text'] ) ? sanitize_text_field( $nudge_post['button_text'] ) : esc_html__( 'Chat with Support 💬', 'aicoso-click-to-chat' ),
			);
		}

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

		$retention_days = isset( $_POST['ctc_chat_analytics']['retention_days'] ) ? absint( $_POST['ctc_chat_analytics']['retention_days'] ) : 365;
		$retention_days = max( 30, min( 730, $retention_days ) );

		$settings['analytics'] = array(
			'enabled'            => isset( $_POST['ctc_chat_analytics']['enabled'] ),
			'retention_days'     => $retention_days,
			'track_ip'           => isset( $_POST['ctc_chat_analytics']['track_ip'] ),
			'dedupe_hours'       => 24,
			'exclude_bots'       => isset( $_POST['ctc_chat_analytics']['exclude_bots'] ),
			'visitor_cookie_ttl' => isset( $settings['analytics']['visitor_cookie_ttl'] ) ? absint( $settings['analytics']['visitor_cookie_ttl'] ) : 30,
		);

		// Update settings.
		update_option( 'ctc_chat_settings', $settings );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		// Store success message in transient (will be displayed after redirect).
		set_transient( 'ctc_chat_settings_message', 'success', 30 );

		// Redirect to the same tab.
		$redirect_url = $this->get_settings_hub_url( 'general', $current_tab );
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
			$ctc_numbers = ctc_chat_normalize_number_record_ids( wp_unslash( $_POST['ctc_numbers'] ) );
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
					'id'          => $number_id,
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
			$ctc_templates      = wp_unslash( $_POST['ctc_chat_templates'] );
			$existing_templates = isset( $settings['message_templates'] ) && is_array( $settings['message_templates'] ) ? $settings['message_templates'] : array();
			$message_templates  = array(
				'single_product' => isset( $ctc_templates['single_product'] ) ? sanitize_textarea_field( $ctc_templates['single_product'] ) : ( $existing_templates['single_product'] ?? '' ),
				'shop'           => isset( $ctc_templates['shop'] ) ? sanitize_textarea_field( $ctc_templates['shop'] ) : ( $existing_templates['shop'] ?? '' ),
				'cart_checkout'  => isset( $ctc_templates['cart_checkout'] ) ? sanitize_textarea_field( $ctc_templates['cart_checkout'] ) : ( $existing_templates['cart_checkout'] ?? '' ),
				'thank_you'      => isset( $ctc_templates['thank_you'] ) ? sanitize_textarea_field( $ctc_templates['thank_you'] ) : ( $existing_templates['thank_you'] ?? '' ),
				'floating'       => isset( $ctc_templates['floating'] ) ? sanitize_textarea_field( $ctc_templates['floating'] ) : ( $existing_templates['floating'] ?? '' ),
				'variations'     => isset( $ctc_templates['variations'] ) ? sanitize_textarea_field( $ctc_templates['variations'] ) : ( $existing_templates['variations'] ?? '' ),
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

		?>
		<div class="ctc-product-meta-box">
			<p>
				<label for="ctc_chat_hide_button">
					<input type="checkbox" name="ctc_chat_hide_button" id="ctc_chat_hide_button" value="1" <?php checked( $hide_button, '1' ); ?> />
					<?php esc_html_e( 'Hide WhatsApp button on this product', 'aicoso-click-to-chat' ); ?>
				</label>
			</p>

			<p>
				<label for="ctc_chat_assigned_number"><?php esc_html_e( 'Assign specific WhatsApp number', 'aicoso-click-to-chat' ); ?></label>
				<select name="ctc_chat_assigned_number" id="ctc_chat_assigned_number" class="widefat">
					<option value=""><?php esc_html_e( 'Default (based on rules)', 'aicoso-click-to-chat' ); ?></option>
					<?php foreach ( $whatsapp_numbers as $number ) : ?>
						<option value="<?php echo esc_attr( $number['id'] ); ?>" <?php selected( $assigned_number, $number['id'] ); ?>>
							<?php echo esc_html( $number['name'] . ' (' . $number['number'] . ')' ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>

			<p>
				<label for="ctc_chat_custom_message"><?php esc_html_e( 'Custom message template', 'aicoso-click-to-chat' ); ?></label>
				<textarea name="ctc_chat_custom_message" id="ctc_chat_custom_message" rows="4" class="widefat"><?php echo esc_textarea( $custom_message ); ?></textarea>
				<span class="description">
					<?php esc_html_e( 'Overrides the default template. Placeholders: {product_name}, {price}, {product_url}', 'aicoso-click-to-chat' ); ?>
				</span>
			</p>
		</div>
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
