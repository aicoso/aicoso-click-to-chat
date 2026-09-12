# AICOSO Click to Chat — Next-Generation Features Documentation

This document provides complete, publication-ready documentation for the selected new features of the **AICOSO Click to Chat** WordPress & WooCommerce plugin. Use this content for user documentation, knowledge base articles, plugin handbooks, or marketing material.

---

## Table of Contents
1. [Core E-Commerce: "Order on WhatsApp" & Abandonment Nudges](#1-core-e-commerce-order-on-whatsapp--abandonment-nudges)
2. [Feature 1: "Notify Me on WhatsApp" (Back in Stock Alerts)](#2-feature-1-notify-me-on-whatsapp-back-in-stock-alerts)
3. [Feature 2: "Chat to Unlock Discount" (Coupon Engine)](#3-feature-2-chat-to-unlock-discount-coupon-engine)
4. [Feature 3: 1-Click "Track My Order on WhatsApp"](#4-feature-3-1-click-track-my-order-on-whatsapp)
5. [Feature 6: Desktop "Scan QR Code to Chat" Modal](#5-feature-6-desktop-scan-qr-code-to-chat-modal)
6. [Feature 7: B2B & Wholesale VIP Routing (Role-Based)](#6-feature-7-b2b--wholesale-vip-routing-role-based)
7. [Feature 9: GDPR & Privacy Compliance Mode](#7-feature-9-gdpr--privacy-compliance-mode)
8. [Feature 10: Live Visual Customizer with Real-Time Preview](#8-feature-10-live-visual-customizer-with-real-time-preview)

---

## 1. Core E-Commerce: "Order on WhatsApp" & Abandonment Nudges

### What It Is
An end-to-end conversational commerce solution that lets shoppers bypass friction-heavy checkout forms and order directly through WhatsApp, while automatically rescuing hesitant shoppers on Cart and Checkout pages with behavioral nudges.

### How It Works
* **Single Product Direct Buy**: A branded "Order via WhatsApp" button is placed adjacent to or below WooCommerce's "Add to Cart" button. When clicked, it captures the selected variation (color, size), quantity, and unit price, sending a clean order inquiry directly to the store’s sales number.
* **Cart Page Checkout**: Shoppers with questions regarding shipping or custom inquiries can click "Checkout via WhatsApp" to send their entire itemized cart (products, quantities, taxes, subtotal, and cart total) to the merchant.
* **Smart Abandonment Nudges**: If a customer remains inactive on the Cart or Checkout page for longer than a specified duration (e.g., 25 seconds) or moves their cursor toward the exit bar (exit-intent), a gentle slide-in bubble prompts: *"Need help completing your order? Chat with our team now!"*

### Admin Configuration
* **Toggle Locations**: Enable/disable independently on Single Product, Cart, and Checkout pages.
* **Button Style & Text**: Customize CTA text (e.g., *"Order on WhatsApp 🛍️"*), colors, and position.
* **Optional Quick Popup**: Prompt the customer for their Name and Delivery City before opening WhatsApp.
* **Nudge Triggers**: Select between Inactivity Timer (seconds), Scroll %, or Exit-Intent.

### Sample WhatsApp Message
```text
🛍️ *NEW ORDER INQUIRY*
--------------------------------
*Product:* Ergonomic Office Chair
*SKU:* CHAIR-ERG-01
*Variation:* Color: Slate Grey
*Quantity:* 1
*Total:* $249.00
--------------------------------
*Customer:* Alex Morgan (City: Austin, TX)
*Link:* https://example.com/product/ergonomic-chair
--------------------------------
_Hello! I would like to place an order for this item via WhatsApp._
```

---

## 2. Feature 1: "Notify Me on WhatsApp" (Back in Stock Alerts)

### What It Is
An automated lead-recovery feature that replaces the disabled "Out of Stock" button on WooCommerce product pages with a high-converting "Notify Me on WhatsApp" button.

### How It Works
1. When a product or variation reaches `0` inventory (`outofstock`), standard "Add to Cart" is hidden.
2. In its place, a distinct button displays: **"Notify Me on WhatsApp When Available"**.
3. Clicking opens a WhatsApp chat with the product's SKU, title, selected variation attributes, and product URL pre-filled.
4. The merchant receives the lead directly in their chat, building a warm prospect list ready to convert as soon as stock is replenished.

### Admin Configuration
* **Enable/Disable**: Automatically detect out-of-stock products or apply selectively by category.
* **Custom Button Label**: Customize text (e.g., *"Alert Me When Back in Stock 🔔"*).
* **Dedicated Restock Agent**: Option to route stock inquiries to an inventory/procurement manager's number rather than the general sales line.

### Sample WhatsApp Message
```text
🔔 *BACK IN STOCK INQUIRY*
--------------------------------
*Product:* Limited Edition Canvas Sneakers
*SKU:* SNK-WHT-42
*Size:* 42 (EU)
*URL:* https://example.com/product/canvas-sneakers
--------------------------------
_Hi! Please notify me on this WhatsApp number as soon as this item is back in stock._
```

---

## 3. Feature 2: "Chat to Unlock Discount" (Coupon Engine)

### What It Is
An interactive incentive system that encourages hesitant visitors to initiate a WhatsApp conversation by offering an instant discount code or promotional voucher.

### How It Works
1. A small promotional chip or pulsating tooltip appears above the WhatsApp widget: *"💬 Chat with us to get 10% OFF your first order!"*
2. Clicking the widget automatically generates or attaches an authorized WooCommerce coupon code into the chat greeting.
3. The store owner can either attach a static coupon (e.g., `SAVE10`) or dynamically link to an active promotional campaign.
4. The visitor starts the chat to claim their discount, giving the merchant an active, consented WhatsApp contact for sales follow-up.

### Admin Configuration
* **Coupon Association**: Select an existing WooCommerce coupon from a dropdown in admin settings.
* **Call-to-Action Teaser**: Configure the floating teaser text, badge color, and display delay.
* **Message Template**: Inject dynamic tokens like `{coupon_code}` and `{discount_value}`.

### Sample WhatsApp Message
```text
🎁 *SPECIAL DISCOUNT INQUIRY*
--------------------------------
*Product:* Premium Espresso Machine
*Exclusive Coupon:* WELCOME10 (10% OFF)
*Page:* https://example.com/shop/espresso-machine
--------------------------------
_Hello! I'm claiming my 10% discount coupon to purchase this product today. Can you assist me?_
```

---

## 4. Feature 3: 1-Click "Track My Order on WhatsApp"

### What It Is
A post-purchase support feature that embeds a dedicated WhatsApp inquiry button on the WooCommerce Order Received (Thank You) page and the customer's "My Account > Orders" view.

### How It Works
1. After completing an order, the customer lands on the WooCommerce Thank You page.
2. A prominent card appears: *"Have questions about your order? Get live delivery updates on WhatsApp."*
3. Clicking the button automatically populates the Order ID, order date, items purchased, and current status (`Processing`, `On hold`, `Completed`).
4. Drastically reduces customer anxiety and support email tickets.

### Admin Configuration
* **Display Placements**: Enable on Thank You page, Customer Account page, and/or Order Confirmation emails.
* **Routing**: Route order support inquiries to a dedicated Customer Care / Logistics number.

### Sample WhatsApp Message
```text
📦 *ORDER STATUS INQUIRY*
--------------------------------
*Order Number:* #10842
*Order Date:* October 14, 2026
*Status:* Processing
*Total:* $89.50 (2 Items)
--------------------------------
_Hello Support! I would like to check on the delivery timeline and shipping tracking for my order._
```

---

## 5. Feature 6: Desktop "Scan QR Code to Chat" Modal

### What It Is
A frictionless desktop bridge that displays a dynamic, high-resolution WhatsApp QR code when desktop visitors click the chat button, enabling them to chat instantly from their phone.

### How It Works
1. Desktop users frequently do not have WhatsApp Web installed or logged in.
2. When a visitor clicks the chat button on a desktop computer, a sleek modal opens instead of immediately forcing a redirect to WhatsApp Web.
3. The modal displays:
   * A dynamic QR code encoding the exact pre-filled message URL.
   * Clear instruction: *"Point your phone camera at this QR code to start chatting."*
   * A secondary link: *"Or open WhatsApp Web on this computer"*.
4. On mobile devices, the button bypasses the modal and opens the native WhatsApp app directly.

### Admin Configuration
* **Enable Desktop Modal**: Toggle on/off for desktop viewports.
* **Custom Modal Branding**: Add store logo inside the modal, change modal title and background styling.

---

## 6. Feature 7: B2B & Wholesale VIP Routing (Role-Based)

### What It Is
A smart routing rule that serves different WhatsApp numbers, buttons, and greetings based on the visitor’s logged-in WordPress user role.

### How It Works
* **Guests & Standard Shoppers (`customer`, `subscriber`)**:
  * Routed to the general customer support or retail sales team.
  * Sees consumer-oriented message templates.
* **Wholesale & Trade Clients (`wholesale_customer`, `b2b_buyer`, `vendor`)**:
  * Routed directly to a dedicated B2B Key Account Manager or VIP hotline.
  * Message templates automatically include the company's registered billing name, wholesale pricing inquiries, and bulk MOQ requests.

### Admin Configuration
* **Role Mapping Interface**: Map specific WordPress user roles to designated WhatsApp numbers in the Numbers Manager.
* **Fallback Rule**: Default routing applied when no user role matches or for guest visitors.

### Sample B2B WhatsApp Message
```text
🏢 *B2B WHOLESALE INQUIRY*
--------------------------------
*Company:* Metro Distributors Ltd.
*Contact Person:* David Vance (Role: Wholesale Partner)
*Product:* Industrial Air Purifier (SKU: AP-IND-500)
*Target Quantity:* 50 units
--------------------------------
_Hello Account Manager! We are preparing a bulk purchase order and need a custom wholesale quotation._
```

---

## 7. Feature 9: GDPR & Privacy Compliance Mode

### What It Is
A built-in privacy protection suite ensuring that your WhatsApp integration complies fully with GDPR, ePrivacy, CCPA, and international data protection laws.

### How It Works
* **Pre-Chat Consent**: Displays a subtle consent notice or checkbox before launching WhatsApp: *"By contacting us via WhatsApp, you agree to our Privacy Policy and consent to data processing for order communication."*
* **Zero-Cookie / Anonymized Analytics**: In the internal analytics system, IP addresses can be anonymized (e.g., `192.168.xxx.xxx`) or completely excluded from the database.
* **Consent Logging**: Records user consent status without storing personally identifiable information (PII).

### Admin Configuration
* **Enable Consent Notice**: Toggle on/off.
* **Custom Consent Text**: Rich text field with markdown/HTML support to link directly to the site's Privacy Policy page.
* **Anonymize Telemetry**: Checkbox to hash visitor IP addresses in the click logs.

---

## 8. Feature 10: Live Visual Customizer with Real-Time Preview

### What It Is
An interactive, WYSIWYG (What-You-See-Is-What-You-Get) preview panel embedded inside the WordPress admin settings dashboard.

### How It Works
1. As the administrator configures widget settings (button color, position, border radius, icon size, agent avatar, greeting text, or badge counter), the changes are reflected **instantly in real-time** inside an on-screen visual preview canvas.
2. Admins can toggle between **Mobile View** and **Desktop View** right in the admin panel to test responsiveness before publishing changes live.
3. Eliminates trial-and-error and prevents broken frontend layouts.

### Admin Experience Features
* **Color Pickers**: Live palette selection with WCAG accessibility contrast warnings.
* **Layout Presets**: One-click selection between Floating Pill, Circular Icon, WhatsApp Chat Bubble, and Full-Width Product Button.
* **Device Switcher**: Instant preview toggle for iPhone, Android, and Desktop widescreen viewports.
