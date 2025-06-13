# Click to Chat for WooCommerce

![Click to Chat](assets/banner-772x250.png)

## Description

Click to Chat enables customers to order products directly through WhatsApp with a single click. This plugin adds WhatsApp buttons to product pages, shop pages, cart, checkout, and more, allowing your customers to easily inquire about products or complete their purchase via WhatsApp.

### Key Features

- **Multiple Button Placements**: Add WhatsApp buttons to single product pages, shop/category pages, cart, checkout, thank you pages, and a floating button option
- **Customizable Appearance**: Fully customize button colors, text, and styling to match your store's design
- **Smart Message Templates**: Pre-filled messages with product details, prices, variations, and more
- **Multiple WhatsApp Numbers**: Assign different WhatsApp numbers to specific products or categories
- **Conditional Display**: Show or hide buttons based on products, categories, tags, or pages
- **Shortcode Support**: Use shortcodes to place WhatsApp buttons anywhere on your site
- **Mobile-Friendly**: Optimized for all devices with responsive design

## Installation

1. Upload the `click-to-chat` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Click to Chat settings to configure your WhatsApp number and button options

## Requirements

- WordPress 6.2 or higher
- WooCommerce 8.2 or higher
- PHP 7.4 or higher

## Configuration

### 1. WhatsApp Numbers

Add one or more WhatsApp numbers in the plugin settings. You can:
- Set a default number for all products
- Assign specific numbers to particular products or categories
- Add a name and description for each number

### 2. Button Settings

Customize how your WhatsApp buttons look:
- Button text
- Background and text colors
- Show/hide WhatsApp icon
- Add custom CSS for advanced styling

### 3. Button Placement

Control where WhatsApp buttons appear:

#### Single Product Pages
- Below add to cart button
- Below price
- Above add to cart button
- Above title
- Below short description

#### Shop/Category Pages
- Enable/disable buttons on shop pages
- Choose button position

#### Cart & Checkout
- Enable/disable buttons on cart page
- Enable/disable buttons on checkout page
- Enable/disable buttons on thank you/order confirmation page

#### Floating Button
- Enable/disable floating button
- Choose position (bottom right, bottom left, etc.)

### 4. Message Templates

Customize the pre-filled messages for different scenarios:

- **Single Product**: Includes product name, price, and URL
- **Product Variations**: Includes selected variation details
- **Shop/Category**: Includes category name and current page URL
- **Cart/Checkout**: Includes cart items list, subtotal, and total
- **Order Confirmation**: Includes order number, date, and items list
- **Floating Button**: Includes current page URL

### 5. Exclusions

Set conditions to hide WhatsApp buttons on specific:
- Pages
- Posts
- Product categories
- Product tags

## Shortcodes

Use these shortcodes to add WhatsApp buttons anywhere on your site:

### Basic Product Button
```
[ctc_button]
```

### Button for Specific Product
```
[ctc_button product_id="123"]
```

### Custom Button Text
```
[ctc_button text="Contact us about this product"]
```

## Frequently Asked Questions

### Does this plugin require a WhatsApp Business API account?
No, this plugin uses the standard WhatsApp URL scheme that works with regular WhatsApp accounts.

### Can I use multiple WhatsApp numbers for different products?
Yes, you can add multiple WhatsApp numbers and assign them to specific products or categories.

### Will the button work on mobile devices?
Yes, the button will open the WhatsApp app on mobile devices and the WhatsApp Web interface on desktop computers.

### Can I customize the message that is sent?
Yes, you can fully customize the message templates for all button types in the plugin settings.

### Is the plugin compatible with WPML/Polylang?
Yes, all text elements are translation-ready.

## Screenshots

1. WhatsApp button on product page
2. Admin settings - General configuration
3. Admin settings - Button appearance
4. Admin settings - Message templates
5. WhatsApp numbers management
6. Floating WhatsApp button

## Changelog

### 1.0.0
* Initial release

## Support

If you have any questions or need assistance, please contact us at support@aicoso.com or visit [our website](https://aicoso.com/).

## License

This plugin is licensed under the GPL v2 or later.

```
Click to Chat is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Click to Chat is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Click to Chat. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
```