# AICOSO Click to Chat

AICOSO Click to Chat connects WooCommerce stores with customers through configurable WhatsApp buttons. Store managers can place buttons throughout the shopping journey, use dynamic message templates, route conversations to different WhatsApp numbers, and review privacy-conscious click analytics.

## Compatibility

- Requires WordPress 6.2 or newer; tested up to WordPress 7.0
- Requires WooCommerce 8.2 or newer; tested up to WooCommerce 10.3.5
- Requires PHP 7.4 or newer
- Current version: 1.2.1

## Key features

- Place WhatsApp buttons on product, shop, cart, checkout, thank-you, and floating-button locations.
- Customize button text, colors, icon visibility, positioning, and responsive presentation.
- Create message templates for products, variations, shop pages, cart and checkout details, order confirmations, and floating buttons.
- Configure multiple WhatsApp numbers and assign them to products, categories, tags, or general enquiries.
- Hide buttons on selected pages, posts, products, categories, or tags.
- Add buttons anywhere with the `[ctc_chat_button]` shortcode.
- Review click KPIs, trends, intent funnel, top products, and top WhatsApp numbers in the Analytics Dashboard.
- Filter the entire dashboard by All numbers, one configured number, or Unattributed clicks.

## Installation

1. Upload the `aicoso-click-to-chat` directory to `/wp-content/plugins/`, or upload the plugin ZIP from **Plugins > Add New Plugin**.
2. Activate **AICOSO Click to Chat** from the WordPress Plugins screen.
3. Open the Click to Chat settings and configure at least one WhatsApp number.
4. Configure button placement, appearance, templates, exclusions, and analytics as required.

## Configuration

### WhatsApp numbers

Add one or more WhatsApp numbers, choose a default number, and optionally assign specific numbers to products, categories, or tags. Each number can have an administrator-facing name and description.

### Button appearance and placement

Configure the button text, colors, icon visibility, and supported storefront positions. Available placements include product pages, shop/category listings, cart, checkout, thank-you pages, and a floating button.

### Message templates

Templates can include relevant product, variation, cart, checkout, order, and page details. The plugin generates the WhatsApp URL locally and does not require a WhatsApp Business API account.

### Exclusions

Hide buttons on selected pages, posts, products, product categories, or product tags.

## Analytics Dashboard

The Analytics Dashboard reports recorded WhatsApp button clicks, unique clicks, high-intent placements, click trends, intent stages, top products, and top WhatsApp numbers.

The number filter scopes every dashboard section consistently:

- **All numbers** includes all recorded clicks, including historical clicks for numbers that were later removed.
- **A configured number** includes only clicks attributed to that stable number identity and displays a masked number label.
- **Unattributed** includes older or fallback click records without a stored number identity.

If a selected number is removed while the dashboard is open, the dashboard returns safely to All numbers and displays a notice. Analytics represent button-click activity only; they do not claim completed sales or rewrite historical attribution.

## Shortcodes

Basic button:

```text
[ctc_chat_button]
```

Button for a specific product:

```text
[ctc_chat_button product_id="123"]
```

Cart-style button:

```text
[ctc_chat_button type="cart"]
```

Custom button text:

```text
[ctc_chat_button text="Contact Us"]
```

## Frequently asked questions

### Does this plugin require a WhatsApp Business API account?

No. The plugin uses standard WhatsApp URLs and works with regular or business WhatsApp accounts.

### Can different products use different WhatsApp numbers?

Yes. Multiple numbers can be assigned to specific products, categories, or tags, with a default number used as a fallback.

### Does the plugin send analytics data to an external service?

No. Click analytics are stored and processed within the WordPress installation.

### Can I customize the generated message?

Yes. Message templates can be customized for the supported button and page contexts.

### Is the plugin translation-ready?

Yes. User-facing strings use the `aicoso-click-to-chat` text domain, and the translation template is provided in `languages/aicoso-click-to-chat.pot`.

## Changelog

### 1.2.1

- **Order on WhatsApp**: Direct purchase button on single product pages with live variation and quantity watcher.
- **Cart & Checkout Conversational Commerce**: Added WhatsApp buttons for cart and checkout with itemized breakdown templates.
- **Abandonment Rescue Nudges**: Smart inactivity timer and exit-intent prompts on Cart & Checkout pages.
- **Back in Stock Alerts**: "Notify Me on WhatsApp" lead capture for out-of-stock products and dynamically selected variable attributes.
- **Coupon Engine**: "Chat to Unlock Discount" promotional badges, teasers, and coupon token injection.
- **1-Click Order Tracking**: Instant tracking cards on Thank You page, My Account > View Order, and My Account > Orders list.
- **Desktop QR Modal**: Scan to Chat QR code modal on desktop with instant generation and WhatsApp Web fallback.
- **GDPR & Privacy Suite**: Pre-chat consent modal/inline notice and IP telemetry anonymization (IPv4/IPv6 masking).
- **Live Visual Customizer**: Real-time WYSIWYG button customizer with interactive Desktop & Mobile device viewport switcher.
- **Custom CSS Editor**: Dedicated stylesheet editor under Advanced Options for frontend tailoring.
- **Bug Fixes**: Fixed WhatsApp newline stripping (`%0A`), multi-number routing default fallback, null product checks, and XSS prevention in analytics table.

### 1.0.3

- Added an Analytics Dashboard filter for All numbers, one masked configured number, or Unattributed clicks, with safe fallback if the selected number is removed.
- Improved dashboard usability with KPI help text, accessible notices, comparison labels, preset handling, unavailable-data states, chart presentation, and responsive behavior.
- Preserved existing Click to Chat settings during plugin updates and initialized only missing defaults.
- Improved WordPress Plugin Check compliance, cross-platform release packaging, PHPCS dependency handling, line-ending consistency, and translation template generation.

### 1.0.1

- Updated compatibility for WordPress 6.8 and WooCommerce 10.3.

### 1.0.0

- Initial release.

## Support

For support, visit [aicoso.com](https://aicoso.com/) or contact support@aicoso.com.

## License

Licensed under the GNU General Public License v3.0 (`GPL-3.0`). See [LICENSE](LICENSE) for the complete license text.
