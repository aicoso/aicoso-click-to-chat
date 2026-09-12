<?php
/**
 * Shared WhatsApp button renderer with analytics attributes.
 *
 * @package ClickToChat
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Button renderer utility.
 */
class CTC_Chat_Button_Renderer {

	/**
	 * Render a WhatsApp button anchor.
	 *
	 * @param string $url     WhatsApp URL.
	 * @param string $type    Button type slug.
	 * @param array  $args    Button args.
	 */
	public static function render( $url, $type, $args = array() ) {
		$settings = get_option( 'ctc_chat_settings', array() );

		$defaults = array(
			'text'          => isset( $settings['button_settings']['text'] ) ? $settings['button_settings']['text'] : __( 'Order via WhatsApp', 'aicoso-click-to-chat' ),
			'show_icon'     => ! isset( $settings['button_settings']['icon'] ) || $settings['button_settings']['icon'],
			'bg_color'      => isset( $settings['button_settings']['bg_color'] ) ? $settings['button_settings']['bg_color'] : '#25D366',
			'text_color'    => isset( $settings['button_settings']['text_color'] ) ? $settings['button_settings']['text_color'] : '#ffffff',
			'extra_classes' => array(),
			'context'       => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$context = wp_parse_args(
			$args['context'],
			array(
				'template_type'   => '',
				'number_id'       => 0,
				'product_id'      => 0,
				'variation_id'    => 0,
				'order_id'        => 0,
				'cart_item_count' => 0,
				'cart_total'      => 0,
				'cart_currency'   => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '',
			)
		);

		$button_classes = array_merge(
			array(
				'ctc-chat-whatsapp-button',
				'ctc-chat-button-' . sanitize_html_class( $type ),
			),
			array_map( 'sanitize_html_class', (array) $args['extra_classes'] )
		);

		if ( $args['show_icon'] ) {
			$button_classes[] = 'ctc-chat-button-with-icon';
		}

		$button_style = 'background-color: ' . esc_attr( $args['bg_color'] ) . '; color: ' . esc_attr( $args['text_color'] ) . ';';
		$data_attrs   = self::build_data_attributes( $type, $context );

		// esc_url() in WordPress core strips %0a and %0d, destroying WhatsApp line breaks.
		// Protect %0A with a temporary token that survives esc_url(), then restore %0A.
		$nl_token     = '__CTC_NL_TOKEN__';
		$tokenized    = str_ireplace( '%0a', $nl_token, $url );
		$sanitized    = esc_url( $tokenized );
		$escaped_link = str_replace( $nl_token, '%0A', $sanitized );

		?>
		<a href="<?php echo $escaped_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped via esc_url with newline token preservation ?>"
			class="<?php echo esc_attr( implode( ' ', $button_classes ) ); ?>"
			style="<?php echo esc_attr( $button_style ); ?>"
			target="_blank"
			rel="noopener"
			<?php echo $data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( $args['show_icon'] ) : ?>
				<span class="ctc-chat-whatsapp-icon">
					<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
						<path fill="currentColor" d="M17.498 14.382c-.301-.15-1.767-.867-2.04-.966-.273-.101-.473-.15-.673.15-.197.295-.771.964-.944 1.162-.175.195-.349.21-.646.075-.3-.15-1.263-.465-2.403-1.485-.888-.795-1.484-1.77-1.66-2.07-.174-.3-.019-.465.13-.615.136-.135.301-.345.451-.523.146-.181.194-.301.297-.496.1-.21.049-.375-.025-.524-.075-.15-.672-1.62-.922-2.206-.24-.584-.487-.51-.672-.51-.172-.015-.371-.015-.571-.015-.2 0-.523.074-.797.359-.273.3-1.045 1.02-1.045 2.475s1.07 2.865 1.219 3.075c.149.195 2.105 3.195 5.1 4.485.714.3 1.27.48 1.704.629.714.227 1.365.195 1.88.121.574-.091 1.767-.721 2.016-1.426.255-.705.255-1.29.18-1.425-.074-.135-.27-.21-.57-.345m-5.446 7.443h-.016c-1.77 0-3.524-.48-5.055-1.38l-.36-.214-3.75.975 1.005-3.645-.239-.375c-.99-1.576-1.516-3.391-1.516-5.26 0-5.445 4.455-9.885 9.942-9.885 2.654 0 5.145 1.035 7.021 2.91 1.875 1.859 2.909 4.35 2.909 6.99-.004 5.444-4.46 9.885-9.935 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.447h.006c6.585 0 11.946-5.336 11.949-11.896 0-3.176-1.24-6.165-3.495-8.411"/>
					</svg>
				</span>
			<?php endif; ?>
			<span class="ctc-chat-button-text"><?php echo esc_html( $args['text'] ); ?></span>
		</a>
		<?php
		// GDPR Privacy Compliance Inline Notice.
		$settings = get_option( 'ctc_chat_settings', array() );
		$privacy  = isset( $settings['privacy_compliance'] ) ? $settings['privacy_compliance'] : array();
		if ( ! empty( $privacy['enabled'] ) && ( $privacy['consent_mode'] ?? 'prompt' ) === 'inline_notice' && 'floating' !== $type ) {
			echo self::get_inline_privacy_notice( $privacy ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Build HTML for GDPR inline privacy notice.
	 *
	 * @param array $privacy Privacy settings.
	 * @return string
	 */
	public static function get_inline_privacy_notice( $privacy ) {
		$policy_url = ! empty( $privacy['custom_policy_url'] ) ? esc_url( $privacy['custom_policy_url'] ) : '';
		if ( empty( $policy_url ) && function_exists( 'get_privacy_policy_url' ) ) {
			$policy_url = esc_url( get_privacy_policy_url() );
		}

		$link_text        = ! empty( $privacy['link_text'] ) ? esc_html( $privacy['link_text'] ) : esc_html__( 'Privacy Policy', 'aicoso-click-to-chat' );
		$policy_link_html = $policy_url ? '<a href="' . $policy_url . '" target="_blank" rel="noopener noreferrer" class="ctc-privacy-link">' . $link_text . '</a>' : $link_text;

		$notice_template = ! empty( $privacy['notice_text'] ) ? $privacy['notice_text'] : esc_html__( 'By chatting with us on WhatsApp, you agree to our {privacy_policy_link} and consent to communication regarding your inquiry.', 'aicoso-click-to-chat' );
		$notice_html     = str_replace( '{privacy_policy_link}', $policy_link_html, esc_html( $notice_template ) );

		return '<div class="ctc-chat-inline-privacy-notice"><span class="ctc-chat-privacy-lock">🔒</span> ' . $notice_html . '</div>';
	}

	/**
	 * Build HTML data attributes for tracking.
	 *
	 * @param string $type    Button type.
	 * @param array  $context Tracking context.
	 * @return string
	 */
	private static function build_data_attributes( $type, $context ) {
		$attrs = array(
			'data-ctc-button-type'     => sanitize_key( $type ),
			'data-ctc-template-type'   => sanitize_key( $context['template_type'] ),
			'data-ctc-number-id'       => absint( $context['number_id'] ),
			'data-ctc-product-id'      => absint( $context['product_id'] ),
			'data-ctc-variation-id'    => absint( $context['variation_id'] ),
			'data-ctc-order-id'        => absint( $context['order_id'] ),
			'data-ctc-cart-count'      => absint( $context['cart_item_count'] ),
			'data-ctc-cart-total'      => is_numeric( $context['cart_total'] ) ? $context['cart_total'] : 0,
			'data-ctc-cart-currency'   => sanitize_text_field( $context['cart_currency'] ),
		);

		$html = '';

		foreach ( $attrs as $key => $value ) {
			$html .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( (string) $value ) );
		}

		return $html;
	}
}
