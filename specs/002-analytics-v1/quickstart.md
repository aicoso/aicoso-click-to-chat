# Quickstart: Click Analytics V1 Development

## Prerequisites

- WordPress Studio site with WooCommerce active
- Plugin path: `sites/pre-prod-enviroment-aicoso/wp-content/plugins/aicoso-click-to-chat`
- Branch: `002-analytics-v1` (from `develop`)

## Local URLs

```bash
studio site status --path "sites/pre-prod-enviroment-aicoso"
```

| Screen | Path |
|--------|------|
| Dashboard | `/wp-admin/admin.php?page=click-to-chat` |
| Reports | `/wp-admin/admin.php?page=click-to-chat-reports` |
| Settings | `/wp-admin/admin.php?page=click-to-chat-settings` |

## Dev workflow

1. Read [spec.md](spec.md), [data-model.md](data-model.md), contracts in `contracts/`.
2. Implement stories 09 → 17 per [plan.md](plan.md).
3. After Story 09, confirm table:

```bash
studio --path "sites/pre-prod-enviroment-aicoso" wp db query "DESCRIBE wp_ctc_chat_clicks"
```

4. After Story 11, click a storefront button and verify:

```bash
studio --path "sites/pre-prod-enviroment-aicoso" wp db query "SELECT id, button_type, product_id, clicked_at FROM wp_ctc_chat_clicks ORDER BY id DESC LIMIT 5"
```

5. After Story 14–15, browser QA with date range changes.

## Seed data (manual)

For dashboard QA without waiting for traffic:

```sql
INSERT INTO wp_ctc_chat_clicks
  (clicked_at, button_type, number_id, product_id, cart_total, cart_currency, page_path, visitor_key, is_unique, is_bot)
VALUES
  (NOW() - INTERVAL 1 DAY, 'product', 1, 686, NULL, 'USD', '/product/sample/', SHA2('test1', 256), 1, 0),
  (NOW() - INTERVAL 2 DAY, 'cart', 1, NULL, 150.00, 'USD', '/cart/', SHA2('test2', 256), 1, 0);
```

Prefer a WP-CLI `wp ctc seed-clicks` command only if needed for repeated QA — optional dev tool, not V1 scope.

## Evidence folder

```text
docs/evidence/analytics-v1/
├── README.md
├── click-capture.md
├── dashboard-qa.md
├── reports-qa.md
└── screenshots/
```

## Copy guidelines (UI)

**Use**

- “WhatsApp clicks”
- “High-intent clicks”
- “Cart value at click”
- “WhatsApp intent funnel”

**Avoid**

- “Conversations”
- “Revenue from WhatsApp”
- “Conversion rate” (without qualifying as click funnel only)

## PHPCS

```bash
cd sites/pre-prod-enviroment-aicoso/wp-content/plugins/aicoso-click-to-chat
vendor/bin/phpcs --standard=phpcs.xml
```
