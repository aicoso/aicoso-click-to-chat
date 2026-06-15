# Feature Specification: Click Analytics V1 (Tracking, Dashboard, Reports)

**Feature Branch**: `002-analytics-v1`

**Created**: 2026-06-12

**Status**: Draft

**Depends on**: Admin native shell (`001-admin-native-redesign`) — Dashboard and Reports routes already exist as placeholders.

**Input**: Ship first-party WhatsApp click analytics only. No config-only dashboard (V0). Dashboard and Reports must be powered by tracked click data from day one.

## Product goal

Give WooCommerce store owners a clear answer to:

1. How often are customers clicking WhatsApp?
2. Where in the purchase journey are those clicks happening?
3. Which products and support lines drive the most WhatsApp intent?
4. What changed vs the previous period?

The plugin must **not** claim conversation counts, reply rates, or order attribution unless WhatsApp Business API integration is added in a future release.

## Clarifications (locked for V1)

| Topic | Decision |
|-------|----------|
| Transport (storefront tracking) | WordPress `admin-ajax.php` — `wp_ajax_nopriv_ctc_chat_track_click` + `wp_ajax_ctc_chat_track_click` |
| Transport (admin analytics) | WordPress admin AJAX only — no public REST analytics routes |
| Capability (admin read) | `manage_woocommerce` when available; `manage_options` fallback |
| Capability (admin export) | Same as read |
| Timezone | Ranges interpreted in WordPress site timezone; SQL uses inclusive datetime boundaries; API returns `timezone_string` |
| Default date range | Last 30 days |
| Comparison period | Prior period of equal length (e.g. previous 30 days) |
| Currency | Store currency from WooCommerce; amounts stored as decimal at click time |
| IP storage | Store SHA-256 hash of IP + site salt; never show raw IP in admin |
| Bot filtering | Skip insert when user agent matches known bot list (filterable) |
| Rate limit | Max 30 click events per visitor key per minute |
| Uniqueness | `is_unique = 1` when no prior click with same `visitor_key` + `button_type` + `product_id` + `order_id` within 24 hours |
| GA coexistence | Keep existing `gtag`/`ga` passthrough; first-party tracking is additive |
| Retention | Default 365 days; configurable 30–730 in Settings → General (analytics section) |
| Empty state | Dashboard and Reports show guided empty states when zero clicks in range — not setup-only content |
| Evidence | `docs/evidence/analytics-v1/` |

## User scenarios

### US-1 — Storefront click capture (P1)

**As a** shopper, **when** I click a WhatsApp button, **the store** records one click event with placement and commerce context before opening WhatsApp.

**Acceptance**

1. Click on any rendered `.ctc-chat-whatsapp-button` fires a non-blocking POST before navigation (`sendBeacon` preferred, `fetch` with `keepalive` fallback).
2. Event includes button type, resolved number ID, product/variation/order/cart context when available.
3. Invalid or missing nonce rejects the request without breaking the WhatsApp link.
4. Thank-you, floating, shortcode, and block cart/checkout buttons are tracked (fixes gap in current `public.js`).
5. Duplicate rapid clicks are rate-limited server-side.

### US-2 — Dashboard decision surface (P2)

**As a** store admin, **I want** a dashboard that loads KPIs and charts from click data **so that** I can see WhatsApp intent health at a glance.

**Acceptance**

1. Route: `admin.php?page=click-to-chat` (Dashboard).
2. Shell renders immediately; sections load via admin AJAX independently.
3. Date range and comparison controls refresh affected sections without full page reload.
4. All metrics derive from `wp_ctc_chat_clicks` — no vanity metrics.
5. Each summary card links to a pre-filtered Reports view.

### US-3 — Reports drill-down (P3)

**As a** store admin, **I want** filterable report tables **so that** I can investigate clicks by placement, product, and WhatsApp number.

**Acceptance**

1. Route: `admin.php?page=click-to-chat-reports` with sub-tabs.
2. Click Log supports date range, button type, number, product search, pagination, CSV export.
3. Aggregate reports (By Placement, By Product, By Number) use server-side pagination.
4. Phone numbers display as configured **labels** only; digits masked in tables.

### US-4 — Privacy, retention, uninstall (P4)

**As a** merchant, **I want** predictable data lifecycle and privacy **so that** analytics comply with store policy.

**Acceptance**

1. Retention cron deletes rows older than configured days.
2. Uninstall deletes clicks table and analytics options.
3. Privacy policy helper text documents click logging (docs only in V1).

## Out of scope (V1)

- WhatsApp conversation / read / reply tracking
- Revenue or order attribution (“sales from WhatsApp”)
- Impressions or view-through metrics
- Per-agent WhatsApp Business API inbox
- Config-only dashboard cards without click data
- Multi-site network aggregation

## Dashboard specification (V1)

### Global controls

| Control | Behavior |
|---------|----------|
| Date range | Presets: 7d, 30d, 90d, custom |
| Compare to | Prior period (default on) |
| Grouping (chart) | Daily (≤31d), weekly (≤90d), monthly (>90d) |

### Section 1 — KPI row (6 cards)

| Key | Label | Definition |
|-----|-------|------------|
| `total_clicks` | WhatsApp Clicks | Count of rows in range |
| `unique_clicks` | Unique Clicks | Count where `is_unique = 1` |
| `high_intent_clicks` | High-Intent Clicks | `button_type` IN (`cart`, `checkout`, `thankyou`) |
| `cart_value_clicked` | Cart Value at Click | Sum of `cart_total` where `cart_total > 0` |
| `mobile_share` | Mobile Share | % clicks where `device_type = mobile` |
| `top_placement` | Top Placement | `button_type` with highest click count (tie: prefer higher intent) |

Each card shows: value, % change vs comparison period, helper text. Comparison shows `—` when prior period has zero baseline.

### Section 2 — Click trend chart

- Series: total clicks, unique clicks (optional toggle)
- X-axis: time buckets per grouping rule
- Y-axis: count
- Tooltip: date, clicks, uniques

### Section 3 — Intent funnel

Horizontal funnel stages (counts only, not page views):

```
Product / Shop  →  Cart  →  Checkout  →  Thank you
```

- **Product / Shop**: `product` + `shop` + `floating` (browsing intent)
- **Cart**: `cart`
- **Checkout**: `checkout`
- **Thank you**: `thankyou`

Label: **“WhatsApp intent funnel (clicks)”** — not conversion rate.

### Section 4 — Top products (max 5)

| Column | Source |
|--------|--------|
| Product | `product_id` → post title |
| Clicks | COUNT |
| Unique | COUNT `is_unique` |
| Link | Filtered Click Log report |

Empty: “No product clicks in this period.”

### Section 5 — Top WhatsApp numbers (max 5)

| Column | Source |
|--------|--------|
| Number label | `number_id` → settings name |
| Clicks | COUNT |
| High-intent | COUNT high-intent types |
| Link | Filtered Click Log |

### Section 6 — Quick actions

Links only (not metrics): Settings → Display, Numbers, Templates, Reports → Click Log.

## Reports specification (V1)

### Tab: Click Log (`report=clicks`)

Default landing tab.

| Column | Notes |
|--------|-------|
| Date | Site TZ formatted |
| Placement | Human label for `button_type` |
| Product | Title or — |
| Order | `#12345` link to WC order edit or — |
| Cart total | Formatted WC price or — |
| Number | Label (masked) |
| Device | mobile / tablet / desktop |
| Page | Truncated URL, full in title attr |

**Filters**: date range, button_type (multi), number_id, product_id, device_type, search (product name / order number).

**Actions**: Export CSV (current filter, max 10k rows per export).

### Tab: By Placement (`report=placements`)

| Column | Definition |
|--------|------------|
| Placement | `button_type` |
| Clicks | COUNT |
| Unique Clicks | COUNT unique |
| Share of Total | % of all clicks |
| High-Intent | subset count |

Sort: clicks DESC default.

### Tab: By Product (`report=products`)

Only rows with `product_id > 0`.

| Column | Definition |
|--------|------------|
| Product | Name + SKU if available |
| Clicks | COUNT |
| Unique Clicks | COUNT unique |
| Last Click | MAX `clicked_at` |

### Tab: By WhatsApp Number (`report=numbers`)

| Column | Definition |
|--------|------------|
| Number | Label |
| Clicks | COUNT |
| Unique Clicks | COUNT unique |
| Top Placement | MODE `button_type` |
| Assigned scope | Summary from settings (default / N products / etc.) |

### Tab: By Page (`report=pages`)

Groups by normalized `page_path` (path only, no query string).

| Column | Definition |
|--------|------------|
| Page path | e.g. `/product/sample/` |
| Clicks | COUNT |
| Top placement | MODE `button_type` |

## Metric dictionary

| Metric | SQL sketch | Caveat |
|--------|------------|--------|
| Total clicks | `COUNT(*)` | Includes repeat clicks |
| Unique clicks | `COUNT(*) WHERE is_unique = 1` | 24h dedupe window |
| High-intent clicks | Filter button types | Proxy for purchase intent |
| Cart value at click | `SUM(cart_total)` | Only when cart context present |
| Mobile share | `mobile / total` | Requires device detection |
| % change | `(current - prior) / prior * 100` | Show “New” if prior = 0 |

## Non-functional requirements

| Area | Target |
|------|--------|
| Track endpoint | p95 < 100ms insert; must not block navigation |
| Dashboard KPI AJAX | p95 < 500ms on 10k rows |
| Click log page | 20 rows/page default |
| Indexing | See `data-model.md` |
| Security | Nonce on track + admin AJAX; sanitize all filters |
| i18n | All labels via `aicoso-click-to-chat` text domain |

## Success criteria

1. Click on storefront button creates row in `wp_ctc_chat_clicks` within 2s.
2. Dashboard KPI totals match raw SQL for same date range.
3. Reports CSV export matches on-screen filtered data.
4. Uninstall removes table and analytics options.
5. Zero clicks shows empty analytics UI — not configuration checklist as primary content.
