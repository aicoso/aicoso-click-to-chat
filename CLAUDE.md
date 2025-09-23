# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Click to Chat is a WordPress plugin that integrates WhatsApp functionality with WooCommerce. It allows customers to contact sellers directly via WhatsApp about products, with pre-filled messages containing product details, cart information, and order details.

## Architecture

### Core Plugin Structure

The plugin follows WordPress coding standards with a modular class-based architecture:

- **Main Plugin File**: `click-to-chat.php` - Entry point, registers activation/deactivation hooks, initializes core classes
- **Admin Layer**: `admin/` - Handles backend functionality, settings pages, and admin UI
- **Public Layer**: `public/` - Frontend assets and public-facing functionality
- **Core Classes**: `includes/` - Business logic and core functionality

### Key Classes and Their Responsibilities

1. **Click_To_Chat** (`click-to-chat.php`): Main plugin class using Singleton pattern, coordinates initialization
2. **CTC_Admin** (`admin/class-admin.php`): Admin menu, settings pages, product meta boxes, AJAX handlers
3. **CTC_Settings** (`admin/class-settings.php`): Settings API integration, form handling, validation
4. **CTC_WhatsApp_Link_Generator** (`includes/class-whatsapp-link-generator.php`): Core logic for generating WhatsApp URLs with context-aware pre-filled messages
5. **CTC_Button_Display** (`includes/class-button-display.php`): Button placement logic across different WooCommerce pages
6. **CTC_Shortcodes** (`includes/class-shortcodes.php`): Shortcode implementations for manual button placement
7. **CTC_Public** (`public/class-public.php`): Frontend asset management and custom CSS injection

### Data Storage

Settings are stored as a single serialized array in WordPress options table under key `ctc_settings`. Structure includes:
- `whatsapp_numbers`: Array of WhatsApp numbers with assignments
- `button_settings`: Visual customization options
- `single_product`, `shop_page`, `cart_page`, etc.: Page-specific settings
- `message_templates`: Customizable message templates with placeholders
- `exclusions`: Rules for hiding buttons on specific pages/categories

### Hook Integration Points

The plugin hooks into WooCommerce at multiple points:
- Product pages: `woocommerce_single_product_summary`, `woocommerce_before_add_to_cart_form`, etc.
- Shop pages: `woocommerce_after_shop_loop_item`
- Cart/Checkout: `woocommerce_proceed_to_checkout`, `woocommerce_review_order_after_submit`
- Order confirmation: `woocommerce_thankyou`

## Development Commands

This is a standard WordPress plugin with no build process. Key development tasks:

```bash
# No build tools or package managers are used
# Plugin follows standard WordPress development practices

# Testing the plugin locally requires:
# 1. A WordPress installation (Local Sites, XAMPP, etc.)
# 2. WooCommerce plugin installed and activated
# 3. This plugin placed in wp-content/plugins/click-to-chat/

# To activate the plugin:
# Go to WordPress Admin > Plugins > Click to Chat > Activate

# For debugging, use WordPress debug constants in wp-config.php:
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

## Key Implementation Details

### WhatsApp URL Generation

The plugin uses WhatsApp's URL scheme: `https://wa.me/{number}?text={encoded_message}`
- Numbers are sanitized to remove non-digits
- Messages support dynamic placeholders replaced at runtime
- Context-aware templates for different page types

### Message Template Placeholders

Templates support various placeholders that get replaced dynamically:
- Product context: `{product_name}`, `{price}`, `{product_url}`, `{variation_details}`
- Cart context: `{cart_items_list}`, `{cart_total}`, `{shipping_cost}`
- Order context: `{order_number}`, `{order_date}`, `{ordered_items_list}`
- General: `{current_page_url}`, `{category_name}`

### JavaScript Integration

- Admin JS (`admin/js/admin.js`): Handles settings page interactions, number management, preview updates
- Public JS (`public/js/public.js`): Variable product handling, dynamic URL updates
- Uses jQuery (loaded by WordPress/WooCommerce)

### Security Considerations

- All user inputs sanitized using WordPress functions (`sanitize_text_field`, `esc_html`, etc.)
- Nonces used for AJAX requests and form submissions
- Capability checks for admin operations (`manage_woocommerce`)
- Direct file access prevention using `ABSPATH` checks

## Testing Considerations

When testing changes:
1. Test with WooCommerce simple and variable products
2. Verify button display across different WooCommerce page types
3. Check message template placeholder replacements
4. Test with multiple WhatsApp numbers and assignment rules
5. Verify exclusion rules work correctly
6. Test shortcode functionality
7. Ensure proper escaping of user inputs in generated URLs