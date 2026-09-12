# Quickstart: Dashboard WhatsApp Number Filter

## Purpose

Validate the implementation against [spec.md](spec.md), [data-model.md](data-model.md), and
[the dashboard filter contract](contracts/dashboard-number-filter.md). Use a disposable local
WordPress/WooCommerce site; the fixture is self-restoring but must never be aimed at
production.

## Prerequisites

- Branch `004-dashboard-number-filter`
- WordPress 6.2+ and WooCommerce 8.2+ active
- Plugin active with analytics enabled
- PHP 7.4+ and Node available
- Composer development dependencies installed for PHPCS
- WP-CLI access to a disposable site

Set `<wordpress-root>` in commands to the local site's WordPress root.

## Static quality gates

From the plugin root:

```powershell
php -l includes/class-ctc-chat-analytics.php
php -l admin/class-ctc-chat-analytics-admin.php
php -l admin/class-ctc-chat-admin.php
php -l admin/views/dashboard-page.php
php -l tests/integration/dashboard-number-filter-fixtures.php
node --check admin/js/analytics-dashboard.js
composer phpcs
git diff --check
```

Expected: every command exits successfully with zero syntax or standards errors.

## Deterministic integration fixture

The implementation phase adds
`tests/integration/dashboard-number-filter-fixtures.php`. It must:

- snapshot and restore `ctc_chat_settings`;
- seed configured IDs 101 and 202, removed historical ID 303, `NULL`, and zero attribution;
- tag fixture rows with a unique visitor-key prefix;
- include distinct current and immediately prior periods, placements, products, cart values,
  device states, and stored uniqueness flags;
- clean up only tagged rows in a `finally` path;
- remain repeatable without changing non-fixture rows or option values.

Run twice:

```powershell
wp --path="<wordpress-root>" eval-file "wp-content/plugins/aicoso-click-to-chat/tests/integration/dashboard-number-filter-fixtures.php"
wp --path="<wordpress-root>" eval-file "wp-content/plugins/aicoso-click-to-chat/tests/integration/dashboard-number-filter-fixtures.php"
```

Expected matrix:

| Scenario | Expected scope |
|----------|----------------|
| All | IDs 101 + 202 + removed 303 + NULL + 0 |
| Number 101 | ID 101 only |
| Number 202 | ID 202 only |
| Unattributed | NULL + 0 only |
| Zero-result range | Valid zero/empty/unavailable states only |
| Removed/unknown/malformed request | All + fallback context/notice marker |

For each scope, assert exact KPI totals and prior values, top placement, every trend bucket,
all funnel stages, ordered top products, and ordered top numbers. Rename/change default for
101 and verify counts stay attached to ID 101. Remove it after selection and verify fallback
to All. Confirm ID 303 remains attributed under All and never appears under Unattributed.

## Security and privacy checks

Capture all five dashboard responses and rendered dashboard markup.

1. An administrator with the existing analytics capability succeeds.
2. A user without the capability is rejected.
3. Missing and invalid nonces are rejected.
4. A display name containing HTML-like text is rendered as text, not executable markup.
5. Search the captured HTML and payloads for each configured full phone value.

Expected: unauthorized requests are rejected in every case; full phone values occur zero
times; only escaped names and masked digits are visible.

## Browser QA

Open:

```text
/wp-admin/admin.php?page=click-to-chat
```

Verify at desktop width, 360px-equivalent width, 100% zoom, and 200% zoom:

1. The labeled selector is visible with zero, one, and two configured numbers.
2. Options are All, current masked configured numbers in settings order, and Unattributed.
3. Keyboard Tab reaches the selector; native selection and Apply work without a pointer.
4. Apply filters every KPI and widget consistently for 101, 202, and Unattributed.
5. Date presets immediately refresh while retaining the staged number selection.
6. Custom dates and comparison changes retain the selected number.
7. A valid zero-result selection shows correct empty states.
8. Remove the selected number in another session, apply, and confirm one warning plus a
   complete All-number dashboard.
9. Throttle requests, rapidly apply 101 then 202, and confirm every final widget shows 202.
10. No tooltip, selector, banner, toolbar, or page causes horizontal scrolling.

Record screenshots and results in `docs/evidence/dashboard-number-filter/browser-qa.md`.

## Index and performance evidence

Get the actual database prefix:

```powershell
wp --path="<wordpress-root>" db prefix
```

Substitute it for `<prefix>` below:

```powershell
wp --path="<wordpress-root>" db query "SHOW INDEX FROM <prefix>ctc_chat_clicks WHERE Key_name = 'number_clicked_at'"
wp --path="<wordpress-root>" db query "EXPLAIN SELECT COUNT(*) FROM <prefix>ctc_chat_clicks WHERE number_id = 101 AND clicked_at BETWEEN '2026-07-01 00:00:00' AND '2026-07-31 23:59:59'"
wp --path="<wordpress-root>" db query "EXPLAIN SELECT COUNT(*) FROM <prefix>ctc_chat_clicks WHERE (number_id IS NULL OR number_id = 0) AND clicked_at BETWEEN '2026-07-01 00:00:00' AND '2026-07-31 23:59:59'"
```

With 100 configured numbers and 100,000 retained rows, perform 20 warmed complete-dashboard
refreshes for All, exact number, and Unattributed. At least 19 of 20 refreshes must complete
all sections within 2 seconds. Record query plans, environment, measurements, and any
variance in `docs/evidence/dashboard-number-filter/performance-results.md`.

## Data-preservation evidence

Before and after fixture/filter usage, compare:

- `SHOW CREATE TABLE` and table indexes;
- total click count plus min/max click ID;
- hashes/counts for all non-fixture rows;
- complete `ctc_chat_settings` value after fixture restoration.

Expected: zero schema, option, or pre-existing row changes.

## Evidence package

```text
docs/evidence/dashboard-number-filter/
├── README.md
├── fixture-results.md
├── browser-qa.md
├── security-privacy.md
├── performance-results.md
└── screenshots/
```

`README.md` records WordPress, WooCommerce, PHP, plugin, database, browser, and timezone
versions; dataset and commands; pass/fail summary; known limitations; unresolved risks; and a
release recommendation. Release is blocked unless all fixture assertions and static checks
pass, full phone exposure is zero, data mutation is zero, browser/accessibility checks pass,
and the 19/20 performance gate is met.