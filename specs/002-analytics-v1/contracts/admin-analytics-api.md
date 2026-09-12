# Contract: Admin Analytics AJAX API

All endpoints require logged-in user with `ctc_chat_analytics_capability()` (`manage_woocommerce` or `manage_options`).

## Shared request parameters

| Param | Type | Required | Notes |
|-------|------|----------|-------|
| `action` | string | yes | See per-endpoint |
| `nonce` | string | yes | `ctc_chat_admin_nonce` |
| `start_date` | string | yes | `Y-m-d` site TZ |
| `end_date` | string | yes | `Y-m-d` inclusive |
| `compare` | bool | no | Default `true` |

Server computes:

- `start_datetime` = start 00:00:00 site TZ → UTC storage query
- `end_datetime` = end 23:59:59 site TZ
- Prior period = equal day count immediately before `start_date`

## Shared response envelope

```json
{
  "success": true,
  "data": {
    "range": {
      "start": "2026-05-13",
      "end": "2026-06-12",
      "compare_start": "2026-04-13",
      "compare_end": "2026-05-12",
      "timezone": "America/New_York"
    },
    "payload": { }
  }
}
```

## Endpoints

### `ctc_analytics_kpis`

**Payload**

```json
{
  "total_clicks": { "value": 420, "compare_value": 380, "delta_pct": 10.5 },
  "unique_clicks": { "value": 310, "compare_value": 290, "delta_pct": 6.9 },
  "high_intent_clicks": { "value": 85, "compare_value": 70, "delta_pct": 21.4 },
  "cart_value_clicked": { "value": 12450.00, "compare_value": 9800.00, "delta_pct": 27.0, "currency": "USD" },
  "mobile_share": { "value": 68.2, "compare_value": 65.0, "delta_pct": 3.2, "format": "percent" },
  "top_placement": { "value": "product", "label": "Product", "count": 200 }
}
```

### `ctc_analytics_trend`

**Extra params**: `grouping` = `day` | `week` | `month`

**Payload**

```json
{
  "series": [
    { "date": "2026-06-01", "clicks": 12, "unique_clicks": 9 },
    { "date": "2026-06-02", "clicks": 18, "unique_clicks": 14 }
  ]
}
```

### `ctc_analytics_funnel`

**Payload**

```json
{
  "stages": [
    { "key": "browsing", "label": "Product / Shop", "count": 300 },
    { "key": "cart", "label": "Cart", "count": 45 },
    { "key": "checkout", "label": "Checkout", "count": 28 },
    { "key": "thankyou", "label": "Thank you", "count": 12 }
  ]
}
```

### `ctc_analytics_top_products`

**Payload**

```json
{
  "items": [
    {
      "product_id": 686,
      "name": "Sample Product",
      "clicks": 42,
      "unique_clicks": 35,
      "report_url": "admin.php?page=click-to-chat-reports&report=clicks&product_id=686&..."
    }
  ]
}
```

### `ctc_analytics_top_numbers`

**Payload**

```json
{
  "items": [
    {
      "number_id": 1,
      "label": "Sales Support",
      "masked_number": "+1 ***** 7890",
      "clicks": 120,
      "high_intent_clicks": 40,
      "report_url": "..."
    }
  ]
}
```

### `ctc_analytics_report_clicks`

Paginated raw log.

**Extra params**: `page`, `per_page` (max 100), filters below.

| Filter | Type |
|--------|------|
| `button_type[]` | array of keys |
| `number_id` | int |
| `product_id` | int |
| `device_type` | string |
| `search` | string (product title or order number) |

**Payload**

```json
{
  "rows": [
    {
      "id": 99,
      "clicked_at": "2026-06-12 14:30:00",
      "button_type": "cart",
      "button_label": "Cart",
      "product_name": null,
      "order_id": null,
      "order_number": null,
      "cart_total_formatted": "$156.00",
      "number_label": "Sales Support",
      "device_type": "mobile",
      "page_url": "https://store.test/cart/"
    }
  ],
  "total": 420,
  "page": 1,
  "per_page": 20
}
```

### `ctc_analytics_report_aggregate`

**Extra params**: `report` = `placements` | `products` | `numbers` | `pages`, plus pagination.

**Payload**

```json
{
  "columns": ["placement", "clicks", "unique_clicks", "share_pct", "high_intent"],
  "rows": [ ... ],
  "total": 5,
  "page": 1,
  "per_page": 20
}
```

### `ctc_analytics_export_csv`

**Extra params**: Same filters as `report_clicks`.

**Response**: `Content-Type: text/csv` streamed download OR admin-ajax returns URL to short-lived export file (prefer direct stream for V1).

Max 10,000 rows per export.

## Error handling

| Code | When |
|------|------|
| 403 | Capability or nonce failure |
| 400 | Invalid date range (end < start, range > 366 days) |
| 200 + empty | Valid range, zero rows |

## Dashboard JS architecture

| Module | Responsibility |
|--------|----------------|
| `admin/js/analytics-dashboard.js` | Range state, section loaders, chart render |
| `admin/js/analytics-reports.js` | Tab routing, tables, export |

Patterns from Affiliate:

- Stale response guard (request sequence id)
- Section-level loading / error / empty states
- No single mega-request — KPIs, trend, funnel, tops load independently

## Deep links (Dashboard → Reports)

| Source | Target |
|--------|--------|
| KPI card | `report=clicks` + date range |
| Top product row | `report=clicks&product_id={id}` |
| Top number row | `report=clicks&number_id={id}` |
| Funnel stage | `report=clicks&button_type[]={types}` |

## UI routes

| Screen | `page` | Query |
|--------|--------|-------|
| Dashboard | `click-to-chat` | — |
| Reports | `click-to-chat-reports` | `report=clicks\|placements\|products\|numbers\|pages` |

Reports sub-tabs use `subsubsub` pattern matching Settings hub.
