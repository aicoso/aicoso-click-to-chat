# Contract: Storefront Click Tracking API

## Endpoint

```
POST /wp-admin/admin-ajax.php
```

## Actions

| Action | Auth |
|--------|------|
| `ctc_chat_track_click` | Public (logged-in and guest) |

## Request

### Headers

- `Content-Type`: `application/x-www-form-urlencoded` or `multipart/form-data`
- Cookie: `ctc_vid` (set by plugin if missing)

### Body parameters

| Param | Type | Required | Sanitization |
|-------|------|----------|--------------|
| `action` | string | yes | literal `ctc_chat_track_click` |
| `nonce` | string | yes | `wp_verify_nonce( ..., 'ctc_chat_track_click' )` |
| `button_type` | string | yes | `sanitize_key`; allowlist |
| `template_type` | string | no | `sanitize_key`; allowlist |
| `number_id` | int | no | `absint` |
| `product_id` | int | no | `absint` |
| `variation_id` | int | no | `absint` |
| `order_id` | int | no | `absint` |
| `cart_item_count` | int | no | `absint` |
| `cart_total` | string | no | `wc_format_decimal` |
| `cart_currency` | string | no | `sanitize_text_field` |
| `page_url` | string | no | `esc_url_raw` |
| `referrer_url` | string | no | `esc_url_raw` |

Server-side enrichment (never trust client for these):

| Field | Source |
|-------|--------|
| `clicked_at` | `current_time( 'mysql', true )` |
| `page_path` | Parsed from `page_url` or `$_SERVER['REQUEST_URI']` |
| `device_type` | `wp_is_mobile()` + tablet heuristic |
| `visitor_key` | Derived from `ctc_vid` cookie |
| `ip_hash` | If `analytics.track_ip` enabled |
| `user_agent_hash` | SHA-256 of `HTTP_USER_AGENT` |
| `is_unique` | Dedupe query |
| `is_bot` | Bot classifier |

### Button `data-*` attributes (render contract)

`render_button()` and shortcode output must include:

```html
<a
  href="..."
  class="ctc-chat-whatsapp-button ctc-chat-button-{type}"
  data-ctc-button-type="product"
  data-ctc-template-type="single_product"
  data-ctc-number-id="3"
  data-ctc-product-id="686"
  data-ctc-variation-id="0"
  data-ctc-order-id="0"
  data-ctc-cart-count="0"
  data-ctc-cart-total="0"
  data-ctc-cart-currency="USD"
  ...
>
```

PHP sets values at render time from link generator context. JS reads attributes on click; does not parse `wa.me` URLs.

## Response

### Success

```json
{
  "success": true,
  "data": {
    "recorded": true,
    "click_id": 12345
  }
}
```

### Ignored (still success — do not break UX)

```json
{
  "success": true,
  "data": {
    "recorded": false,
    "reason": "rate_limited"
  }
}
```

Reasons: `rate_limited`, `bots_excluded`, `analytics_disabled`, `invalid_button_type`

### Error

```json
{
  "success": false,
  "data": {
    "message": "Invalid nonce."
  }
}
```

HTTP 403 for nonce failure; HTTP 400 for invalid allowlist values.

## Client implementation (`public/js/tracking.js`)

```javascript
// On click capture phase:
// 1. preventDefault NOT used — WhatsApp must open
// 2. build payload from data-* + window.location.href + document.referrer
// 3. navigator.sendBeacon(url, body) OR fetch keepalive
// 4. allow default navigation
```

Enqueue only when `analytics.enabled` and plugin enabled.

### Localized script object

```php
wp_localize_script( 'ctc-chat-tracking', 'ctc_chat_tracking', array(
    'ajaxurl'  => admin_url( 'admin-ajax.php' ),
    'nonce'    => wp_create_nonce( 'ctc_chat_track_click' ),
    'enabled'  => true,
) );
```

### Visitor cookie

Set on `wp_enqueue_scripts` if missing:

- Name: `ctc_vid`
- Value: `bin2hex(random_bytes(16))`
- Expiry: `analytics.visitor_cookie_ttl` days

## Rate limiting

Before insert:

```sql
SELECT COUNT(*) FROM ctc_chat_clicks
WHERE visitor_key = :key
AND clicked_at >= NOW() - INTERVAL 1 MINUTE
```

If count >= 30 → return `recorded: false, reason: rate_limited`.

## Bot exclusion

When `analytics.exclude_bots` true, skip insert if UA matches:

- WordPress `wp_is_bot()` when available
- Filter: `ctc_chat_is_bot_user_agent` (bool)

## Security notes

- No admin capability required for track endpoint.
- Do not expose aggregate data on this action.
- `order_id` validated: if provided, order must exist (prevents junk IDs).
- `product_id` validated: must be `product` post type when > 0.

## GA passthrough (unchanged)

Existing `trackButtonClicks()` in `public.js` may move into `tracking.js` but remains optional and separate from first-party storage.
