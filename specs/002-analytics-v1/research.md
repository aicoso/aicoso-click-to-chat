# Research: Click Analytics V1 Baseline

**Feature**: 002-analytics-v1  
**Date**: 2026-06-12

## Current state

### Admin routes (ready)

| Slug | Handler | View |
|------|---------|------|
| `click-to-chat` | `render_dashboard_page()` | Placeholder |
| `click-to-chat-reports` | `render_reports_page()` | Placeholder |

Shell (header, sidebar, breadcrumbs) from `001-admin-native-redesign` is production-ready.

### Data storage today

| Key | Contents |
|-----|----------|
| `ctc_chat_settings` | Config only — no events |
| `_ctc_chat_*` post meta | Per-product overrides |

No custom tables. `uninstall.php` deletes options + post meta only.

### Storefront tracking today

`public/js/public.js` → `trackButtonClicks()`:

- Fires only to `ga` / `gtag` if present
- Captures `button_type` (missing `thankyou`, `shortcode`)
- Does **not** POST to WordPress

Buttons rendered in `class-ctc-chat-button-display.php` → `render_button()`:

- No `data-*` analytics attributes
- Types: product, shop, cart, checkout, thankyou, floating

### Rich context available at render (unused for analytics)

Link generator already resolves:

- Product name, price, URL, variations
- Cart items, subtotal, tax, shipping, total
- Order number, date, items, coupon, total
- WhatsApp number via assignment rules + product meta

### Reference: Affiliate Program

- Table: `apf_affiliate_clicks` with `clicked_at`, device, referrer, IP, uniqueness
- Dashboard: async admin AJAX, KPI cards, charts, drill-down links
- Pattern to reuse: date TZ handling, capability gate, section loaders — **not** schema or UI copy

## Gaps to close for V1

1. Custom click table + install lifecycle
2. Server-side track endpoint with privacy controls
3. `data-*` on all button surfaces including blocks + shortcode
4. Analytics query class
5. Dashboard + Reports UI replacing placeholders
6. Settings for enable / retention / IP hashing

## Decisions rejected

| Idea | Reason |
|------|--------|
| V0 config-only dashboard | User explicitly chose V1-only |
| REST API for analytics | Affiliate uses admin AJAX; single trust boundary |
| Store `user_id` on clicks | Privacy; cookie-based visitor key sufficient for V1 |
| Conversation tracking | Not available without WhatsApp Business API |
| Order attribution | Would mislead merchants |

## Open questions (none blocking plan)

- Chart library: default to CSS bar chart in V1 to avoid new dependency; revisit if design needs line curves.
- Block checkout buttons: confirm `cart-checkout-blocks.js` renders links with classes matching `.ctc-chat-whatsapp-button` — verify during Story 10.
