# Data Model: Click Analytics V1

## Storage overview

| Store | Purpose |
|-------|---------|
| `{prefix}ctc_chat_clicks` | Append-only click event log |
| `ctc_chat_settings` | Existing; add `analytics` key |
| `ctc_chat_db_version` | Schema version for `dbDelta` migrations |

No separate aggregate tables in V1 — dashboard and reports query the clicks table with indexes.

## Table: `{prefix}ctc_chat_clicks`

Created on plugin activation / upgrade via `dbDelta`.

```sql
CREATE TABLE {prefix}ctc_chat_clicks (
  id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  clicked_at DATETIME NOT NULL,
  button_type VARCHAR(20) NOT NULL,
  template_type VARCHAR(30) DEFAULT NULL,
  number_id BIGINT(20) UNSIGNED DEFAULT NULL,
  product_id BIGINT(20) UNSIGNED DEFAULT NULL,
  variation_id BIGINT(20) UNSIGNED DEFAULT NULL,
  order_id BIGINT(20) UNSIGNED DEFAULT NULL,
  cart_item_count SMALLINT(5) UNSIGNED DEFAULT NULL,
  cart_total DECIMAL(14,4) DEFAULT NULL,
  cart_currency VARCHAR(10) DEFAULT NULL,
  page_url TEXT DEFAULT NULL,
  page_path VARCHAR(255) DEFAULT NULL,
  referrer_url TEXT DEFAULT NULL,
  device_type VARCHAR(20) DEFAULT NULL,
  visitor_key VARCHAR(64) NOT NULL,
  ip_hash VARCHAR(64) DEFAULT NULL,
  user_agent_hash VARCHAR(64) DEFAULT NULL,
  is_unique TINYINT(1) NOT NULL DEFAULT 0,
  is_bot TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY clicked_at (clicked_at),
  KEY button_type_clicked_at (button_type, clicked_at),
  KEY product_clicked_at (product_id, clicked_at),
  KEY number_clicked_at (number_id, clicked_at),
  KEY order_id (order_id),
  KEY visitor_key_clicked_at (visitor_key, clicked_at),
  KEY page_path_clicked_at (page_path(191), clicked_at)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Column definitions

| Column | Type | Required | Description |
|--------|------|----------|-------------|
| `id` | bigint | yes | Auto-increment primary key |
| `clicked_at` | datetime | yes | UTC stored; displayed in site TZ |
| `button_type` | varchar(20) | yes | See enum below |
| `template_type` | varchar(30) | no | Message template key used for link |
| `number_id` | bigint | no | ID from `whatsapp_numbers[].id` in settings |
| `product_id` | bigint | no | WC product post ID |
| `variation_id` | bigint | no | WC variation ID when applicable |
| `order_id` | bigint | no | WC order on thank-you / checkout context |
| `cart_item_count` | smallint | no | Line item count at click |
| `cart_total` | decimal | no | Cart/order total at click (excl. formatting) |
| `cart_currency` | varchar(10) | no | ISO currency code |
| `page_url` | text | no | Full canonical page URL |
| `page_path` | varchar(255) | no | Path for grouping (`parse_url` path) |
| `referrer_url` | text | no | `document.referrer` when present |
| `device_type` | varchar(20) | no | `mobile`, `tablet`, `desktop`, `unknown` |
| `visitor_key` | varchar(64) | yes | Cookie-based anonymous key (see tracking contract) |
| `ip_hash` | varchar(64) | no | `hash_hmac('sha256', $ip, wp_salt())` |
| `user_agent_hash` | varchar(64) | no | SHA-256 of user agent |
| `is_unique` | tinyint | yes | 1 if first click in 24h dedupe window |
| `is_bot` | tinyint | yes | 1 if classified bot (row may be skipped entirely in V1) |

### `button_type` enum (allowlist)

| Value | Source |
|-------|--------|
| `product` | Single product button |
| `shop` | Shop loop button |
| `cart` | Cart page / cart block |
| `checkout` | Checkout page / checkout block |
| `thankyou` | Thank-you page |
| `floating` | Floating footer button |
| `shortcode` | `[ctc_chat_button]` |

### `template_type` enum (allowlist)

| Value | When |
|-------|------|
| `single_product` | Simple product message |
| `variations` | Variable product with selection |
| `shop` | Shop template |
| `cart_checkout` | Cart or checkout template |
| `thank_you` | Thank-you template |
| `floating` | Floating template |
| `custom` | Product meta custom message or shortcode override |

## Settings extension: `ctc_chat_settings['analytics']`

```php
'analytics' => array(
    'enabled'            => true,   // Master switch; when false, no insert + dashboard shows disabled notice
    'retention_days'     => 365,    // 30–730
    'track_ip'           => true,   // When false, ip_hash stored as null
    'dedupe_hours'       => 24,     // Window for is_unique (fixed in V1, not UI)
    'exclude_bots'       => true,
    'visitor_cookie_ttl' => 30,     // Days for ctc_vid cookie
),
```

Defaults merged on activation via existing `ctc_chat_merge_settings()`.

## Entity relationships

```
ClickEvent
  ├── resolves Number (settings whatsapp_numbers[id])
  ├── resolves Product (wc product post)
  ├── resolves Order (wc order)
  └── grouped by page_path, button_type, clicked_at buckets

DashboardMetric
  └── aggregated from ClickEvent queries

ReportRow
  └── aggregated or raw ClickEvent per report tab
```

## Visitor identity (no logged-in PII)

| Mechanism | Detail |
|-----------|--------|
| Cookie `ctc_vid` | Random 32-byte hex; `httponly`, `secure` when SSL, `samesite=Lax` |
| Logged-in users | Same cookie; **do not** store `user_id` in V1 |
| `visitor_key` | `hash('sha256', ctc_vid + site_url)` |

## Uniqueness algorithm

On insert, before write:

```
is_unique = 1 IF NOT EXISTS row WHERE
  visitor_key = :visitor_key
  AND button_type = :button_type
  AND COALESCE(product_id, 0) = COALESCE(:product_id, 0)
  AND COALESCE(order_id, 0) = COALESCE(:order_id, 0)
  AND clicked_at >= NOW() - INTERVAL dedupe_hours HOUR
```

## Retention job

- Hook: `ctc_chat_prune_click_events` daily via `wp_schedule_event`
- Delete: `WHERE clicked_at < NOW() - retention_days`
- Batch: 1000 rows per run until complete

## Indexes rationale

| Index | Query pattern |
|-------|---------------|
| `clicked_at` | Date range scans |
| `button_type, clicked_at` | Placement reports, funnel |
| `product_id, clicked_at` | Product report |
| `number_id, clicked_at` | Number report |
| `page_path, clicked_at` | Page report |
| `visitor_key, clicked_at` | Uniqueness check, rate limit |

## Dashboard query contracts

### KPI bundle (`ctc_analytics_kpis`)

Single request returns all six KPI values + comparison deltas for range `[start, end]` and prior `[compare_start, compare_end]`.

### Trend series (`ctc_analytics_trend`)

Returns array of `{ date, clicks, unique_clicks }` bucketed by grouping.

### Funnel (`ctc_analytics_funnel`)

Returns `{ browsing, cart, checkout, thankyou }` counts.

### Top lists (`ctc_analytics_top_products`, `ctc_analytics_top_numbers`)

Limit 5; ties broken by `MAX(clicked_at)`.

## Reports query contracts

| Report | GROUP BY | Default ORDER BY |
|--------|----------|------------------|
| Click log | — (raw rows) | `clicked_at DESC` |
| Placements | `button_type` | clicks DESC |
| Products | `product_id` | clicks DESC |
| Numbers | `number_id` | clicks DESC |
| Pages | `page_path` | clicks DESC |

## Privacy display rules

| Field | Admin display |
|-------|---------------|
| `number_id` | Settings `name` + masked phone (`+91 ***** 4321`) |
| `page_url` | Truncate to 60 chars in table |
| `ip_hash` | Never shown |
| `visitor_key` | Never shown |

## Migration

| Version | Change |
|---------|--------|
| `1.0.0` | Initial `ctc_chat_clicks` table |

`ctc_chat_db_version` option drives `dbDelta` on `plugins_loaded` priority 5.

## Uninstall cleanup

Add to `uninstall.php`:

- `DROP TABLE IF EXISTS {prefix}ctc_chat_clicks`
- `delete_option('ctc_chat_db_version')`
- Clear scheduled `ctc_chat_prune_click_events`
- Remove `analytics` from settings OR delete entire `ctc_chat_settings` (existing behavior)
