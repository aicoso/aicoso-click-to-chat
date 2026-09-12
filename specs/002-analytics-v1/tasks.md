# Tasks: Click Analytics V1

Checklist derived from [plan.md](plan.md). Mark stories in `stories/story-09` … `story-17` when implementation starts.

## Phase 1 — Data foundation

- [ ] **T01** Create `includes/class-ctc-chat-install.php` with `dbDelta` for `ctc_chat_clicks`
- [ ] **T02** Wire install on activation + `plugins_loaded` version check
- [ ] **T03** Register daily cron `ctc_chat_prune_click_events`
- [ ] **T04** Update `uninstall.php` — drop table, delete `ctc_chat_db_version`, clear cron
- [ ] **T05** Add `analytics` defaults to `ctc_chat_get_default_settings()`

## Phase 2 — Capture pipeline

- [ ] **T06** Add `$context` parameter to `render_button()` and output `data-ctc-*` attributes
- [ ] **T07** Pass context from all `CTC_Chat_Button_Display` display methods
- [ ] **T08** Add `get_number_id_for_phone()` helper on link generator or tracker
- [ ] **T09** Update shortcode button output with `button_type=shortcode`
- [ ] **T10** Update cart/checkout blocks for tracking context if needed
- [ ] **T11** Create `includes/class-ctc-chat-tracker.php` — insert, dedupe, rate limit, bot filter
- [ ] **T12** Register `wp_ajax_nopriv_ctc_chat_track_click` + logged-in variant
- [ ] **T13** Create `public/js/tracking.js` — sendBeacon, read data-*, GA passthrough
- [ ] **T14** Set `ctc_vid` cookie in `CTC_Chat_Public`
- [ ] **T15** Remove duplicate tracker from `public/js/public.js`

## Phase 3 — Query + API

- [ ] **T16** Create `includes/class-ctc-chat-analytics.php` — KPI, trend, funnel, tops, log, aggregates
- [ ] **T17** Create `admin/class-ctc-chat-analytics-admin.php` — all AJAX actions
- [ ] **T18** Implement `ctc_chat_analytics_capability()`
- [ ] **T19** Implement CSV export with 10k cap
- [ ] **T20** Add date range validation (max 366 days)

## Phase 4 — Dashboard

- [ ] **T21** Rewrite `admin/views/dashboard-page.php` section shell
- [ ] **T22** Create `admin/js/analytics-dashboard.js`
- [ ] **T23** KPI cards component + comparison deltas
- [ ] **T24** Trend chart (CSS/SVG bars)
- [ ] **T25** Intent funnel visualization
- [ ] **T26** Top products + top numbers tables
- [ ] **T27** Quick action links
- [ ] **T28** Empty state when zero clicks
- [ ] **T29** Enqueue dashboard assets in `class-ctc-chat-admin.php`

## Phase 5 — Reports

- [ ] **T30** Rewrite `admin/views/reports-page.php` with subsubsub tabs
- [ ] **T31** Create `admin/js/analytics-reports.js`
- [ ] **T32** Click Log table + filters + pagination
- [ ] **T33** By Placement aggregate table
- [ ] **T34** By Product aggregate table
- [ ] **T35** By WhatsApp Number aggregate table
- [ ] **T36** By Page aggregate table
- [ ] **T37** Export CSV button on Click Log
- [ ] **T38** Deep link query arg parsing from Dashboard

## Phase 6 — Settings + release

- [ ] **T39** Analytics settings fields in Settings → General
- [ ] **T40** Save handler for analytics settings
- [ ] **T41** Dashboard disabled state when analytics off
- [ ] **T42** Update `readme.txt` / `readme.md`
- [ ] **T43** Evidence package under `docs/evidence/analytics-v1/`
- [ ] **T44** PHPCS + `php -l` pass on all new PHP

## Verification commands

```bash
# Table exists after activation
studio --path "sites/pre-prod-enviroment-aicoso" wp db query "SHOW TABLES LIKE '%ctc_chat_clicks%'"

# Row count after test click
studio --path "sites/pre-prod-enviroment-aicoso" wp db query "SELECT COUNT(*) FROM wp_ctc_chat_clicks"

# PHP syntax
find sites/pre-prod-enviroment-aicoso/wp-content/plugins/aicoso-click-to-chat/includes -name '*.php' -newer ... -exec php -l {} \;
```

## Browser QA routes

| Route | Expected |
|-------|----------|
| `admin.php?page=click-to-chat` | Dashboard with KPIs |
| `admin.php?page=click-to-chat-reports` | Click Log default tab |
| `admin.php?page=click-to-chat-reports&report=products` | Product aggregate |
| Product page → click WhatsApp | New DB row |
