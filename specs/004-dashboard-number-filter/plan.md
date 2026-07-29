# Implementation Plan: Dashboard WhatsApp Number Filter

**Branch**: `004-dashboard-number-filter` | **Date**: 2026-07-28 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/004-dashboard-number-filter/spec.md`

## Summary

Add an always-visible, accessible WhatsApp number selector to the Analytics Dashboard and
apply its scope consistently to KPIs, prior-period values, click trend, intent funnel, top
products, and top numbers. The dashboard sends one canonical `number_filter` token with each
existing admin analytics request. The protected request boundary validates the token against
current configured number identities, resolves invalid or removed selections to All numbers,
and returns filter context for an accessible fallback notice. All dashboard queries reuse the
existing prepared filter builder and `(number_id, clicked_at)` index. No tracking, storage,
settings, retention, storefront, or database migration change is required.

## Technical Context

**Language/Version**: PHP 7.4+; browser JavaScript compatible with WordPress 6.2+ admin

**Primary Dependencies**: WordPress admin AJAX, capability and nonce APIs, `$wpdb`, existing
WooCommerce/configured-number settings, existing jQuery admin assets; no new dependency

**Storage**: Existing nullable `number_id` in `{prefix}ctc_chat_clicks` and existing
`ctc_chat_settings['whatsapp_numbers']`; read-only for this feature

**Testing**: WP-CLI integration fixture for dashboard number scopes, PHP syntax checks,
repository PHPCS, `node --check`, SQL `EXPLAIN`/timing evidence, and authenticated browser QA

**Target Platform**: WordPress 6.2+, WooCommerce 8.2+, PHP 7.4+, current supported desktop and
narrow WordPress admin viewports

**Project Type**: WordPress/WooCommerce plugin with PHP-rendered admin UI and asynchronous
dashboard sections

**Performance Goals**: Complete all filtered dashboard sections within 2 seconds in at least
95% of supported test runs with 100 configured numbers and 100,000 retained click rows

**Constraints**: Preserve existing analytics endpoint actions and response fields; preserve
All numbers semantics; never expose full phone numbers; use one number scope for all widgets
and both comparison periods; retain independent section loading; no schema migration, new
runtime dependency, storefront request, or saved user preference

**Scale/Scope**: One dashboard filter, three scope modes, five admin AJAX endpoints, six KPI
families plus four dashboard widgets, up to 100 configured numbers and 100,000 retained rows

## Constitution Check

*GATE: PASS before Phase 0 research. Re-checked after Phase 1 design: PASS.*

- **Backward-Compatible Store Contracts — PASS**: Existing storefront tracking, WhatsApp
  links, shortcodes, hooks, routes, option/meta keys, click schema, retention, and public
  selectors remain unchanged. Dashboard actions and existing response fields are additive;
  omitted `number_filter` resolves to the exact existing All numbers behavior. No migration
  or historical rewrite occurs.
- **WordPress/WooCommerce-Native Architecture — PASS**: The design reuses the existing
  capability/nonce-protected admin AJAX boundary, `$wpdb` prepared queries, configured-number
  settings, translation functions, native `<select>`, established plugin views/classes, and
  dashboard-only assets. No dependency or new architectural boundary is introduced.
- **Security, Privacy, and Data Integrity — PASS**: The server strictly validates the complete
  filter token and configured ID membership before querying. Queries remain prepared. Labels
  are escaped and contain only admin display names plus masked digits. Invalid/stale input
  falls back to All with a notice; it never mutates click or settings data. Retention, export,
  uninstall, and deletion behavior remain unchanged.
- **Incremental and Verifiable Delivery — PASS**: P1 can demonstrate one-number filtering,
  P2 adds All/Unattributed reconciliation, and P3 proves like-for-like comparison. A seeded
  integration matrix, syntax/standards checks, query evidence, accessibility/responsive QA,
  and stale-response/fallback QA are required.
- **Evidence-Based Releases — PASS**: Reproducible commands, fixture results, performance
  observations, browser routes/screenshots, privacy checks, limitations, and release
  recommendation will be recorded under `docs/evidence/dashboard-number-filter/`. User-facing
  help/release notes must describe click filtering only and make no conversion claim.

**Post-design re-check**: PASS. The data model and contract preserve all existing store and
analytics contracts, define one safe request boundary, require prepared filtering and masked
output, and include proportional integration, performance, and browser evidence.

## Project Structure

### Documentation (this feature)

```text
specs/004-dashboard-number-filter/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── checklists/
│   └── requirements.md
└── contracts/
    └── dashboard-number-filter.md
```

### Source Code (repository root)

```text
admin/
├── class-ctc-chat-admin.php                 # dashboard i18n additions, if client notice needs them
├── class-ctc-chat-analytics-admin.php       # validate scope; pass filter; attach response context
├── views/dashboard-page.php                 # always-visible masked native selector + notice region
├── js/analytics-dashboard.js                # request state, fallback retry/notice, failure handling
└── css/admin.css                             # selector layout and narrow-viewport behavior
includes/
└── class-ctc-chat-analytics.php             # shared prepared scope filter across dashboard queries
tests/
└── integration/
    └── dashboard-number-filter-fixtures.php # seeded scope/comparison regression matrix
docs/
└── evidence/dashboard-number-filter/        # fixture, performance, browser, privacy evidence
```

**Structure Decision**: Extend the existing analytics admin, query service, dashboard view,
and dashboard asset boundaries. Keep reports and storefront tracking unchanged. Add one
focused integration fixture and one evidence package; no new production class is warranted.

## Implementation Strategy

1. Render the native selector on first paint from normalized configured numbers. Use values
   `all`, `unattributed`, and positive decimal stable IDs; display current admin names and
   masked digits only. Keep All and Unattributed visible even with zero configured numbers.
2. Add a polite dashboard filter notice region after the toolbar. Reuse the plugin-native
   banner design for the localized invalid/removed selection message; do not use WordPress's
   relocatable `.notice` class.
3. Extend dashboard request state locally in `analytics-dashboard.js`; do not change the
   reports-oriented shared range contract. Apply the staged selection with Apply, retain it
   across presets/date/comparison changes, and keep the existing request-generation guard.
4. Normalize `number_filter` once at the protected admin boundary:
   - omitted or `all` → no number clause;
   - `unattributed` → null/zero attribution clause;
   - positive decimal ID present in normalized current settings → exact number clause;
   - malformed, tampered, stale, or removed ID → All plus fallback metadata/message.
5. Pass one internal filter object into every dashboard service call. Append optional filter
   parameters to existing public method signatures so current callers remain compatible.
6. Reuse the existing date/filter SQL builder for KPI totals and top placement, both prior
   and current KPI periods, trend, funnel, top products, and top numbers. Use
   `(number_id IS NULL OR number_id = 0)` for Unattributed and `number_id = %d` for a number.
7. Add non-sensitive `filter_context` metadata to all five successful endpoint payloads. If
   any response reports fallback, ignore the affected batch, set the UI to All, show one
   warning, and issue one fresh All batch. This prevents deletion between parallel requests
   from producing mixed widget scopes. Add request-failure handlers so sections cannot remain
   indefinitely in Loading state.
8. Preserve Top WhatsApp Numbers semantics: All ranks numbers, an exact number returns at
   most that row, and Unattributed returns at most the Unattributed row. Do not add misleading
   report deep links for Unattributed.
9. Add integration fixtures and browser/performance evidence before release. Confirm All
   results are regression-identical, no full number appears, historical removed IDs stay in
   All but are not selectable, and no data is written by filter usage.

## Phase Delivery

### Phase 0 — Research

Complete [research.md](research.md) with decisions for scope tokens, validation/fallback,
query reuse, async consistency, accessibility/privacy, compatibility, and verification. No
`NEEDS CLARIFICATION` items remain.

### Phase 1 — Design and Contracts

- Define existing entities, transient filter state, validation rules, and transitions in
  [data-model.md](data-model.md).
- Define additive admin request/response behavior in
  [contracts/dashboard-number-filter.md](contracts/dashboard-number-filter.md).
- Define reproducible implementation validation in [quickstart.md](quickstart.md).
- Refresh the managed Spec Kit context in `AGENTS.md` to this plan.

### Phase 2 — Task Planning Boundary

`/speckit-plan` stops after design. `/speckit-tasks` will decompose the strategy into
story-traceable implementation, fixture, evidence, and release-readiness tasks.

## Complexity Tracking

No constitution violations or exceptions require justification.