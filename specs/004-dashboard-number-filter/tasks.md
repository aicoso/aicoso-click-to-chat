# Tasks: Dashboard Analytics Filter by WhatsApp Number

**Input**: Design documents from `/specs/004-dashboard-number-filter/`

**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/dashboard-number-filter.md`, `quickstart.md`

**Verification**: This feature changes the scope of every dashboard query and must be verified with a self-restoring WordPress fixture, request-boundary security checks, browser QA, and performance evidence.

**Organization**: Tasks are grouped by user story so each increment can be implemented and verified independently.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel because it changes a different file and has no unmet dependency
- **[Story]**: Maps the task to User Story 1, 2, or 3
- Every task includes an exact repository path

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Establish the repeatable fixture and evidence locations used by all stories.

- [X] T001 Create the self-restoring WP-CLI fixture scaffold with capability/nonce request helpers, settings snapshot/restore, unique visitor-prefix cleanup in `tests/integration/dashboard-number-filter-fixtures.php`
- [X] T002 [P] Create the evidence index and result templates in `docs/evidence/dashboard-number-filter/README.md`, `docs/evidence/dashboard-number-filter/fixture-results.md`, `docs/evidence/dashboard-number-filter/browser-qa.md`, `docs/evidence/dashboard-number-filter/security-privacy.md`, and `docs/evidence/dashboard-number-filter/performance-results.md`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Add the shared filter contract, validation boundary, and fixture data required by every story.

**CRITICAL**: No user story work begins until this phase is complete.

- [X] T003 Extend `tests/integration/dashboard-number-filter-fixtures.php` with deterministic current/prior date ranges and rows for configured number IDs 101 and 202, removed historical ID 303, `NULL` and zero number IDs, product/placement/cart/device/is_unique variants, a zero-result range, and reusable row-count/content-fingerprint helpers for proving filter use does not mutate analytics records
- [X] T004 [P] Add labels and messages for All numbers, Unattributed, unavailable/stale selections, retry failures, and the number-filter control using WordPress internationalization APIs and the `aicoso-click-to-chat` text domain in `admin/class-ctc-chat-admin.php`
- [X] T005 Implement strict complete-token validation against normalized current number settings in `admin/class-ctc-chat-analytics-admin.php`: resolve omitted or `all` to All, `unattributed` to Unattributed, a positive configured ID to Exact, and malformed, tampered, unknown, zero, negative, decimal, array, stale, or removed input safely to All; return `requested`, `resolved`, `mode`, `fell_back`, and `message` filter context without querying from unvalidated input
- [X] T006 Extend `CTC_Chat_Analytics::build_filter_sql()` and the optional filter arguments of `get_kpis()`, `get_kpi_values()`, `get_trend()`, `get_funnel()`, `get_top_products()`, and `get_top_numbers()` without changing unfiltered callers in `includes/class-ctc-chat-analytics.php`

**Checkpoint**: The fixture, server-side validation contract, localized copy, and backward-compatible query interfaces are ready.

---

## Phase 3: User Story 1 - Analyze One WhatsApp Number (Priority: P1) MVP

**Goal**: An administrator can select a configured WhatsApp number and every dashboard metric and widget shows only clicks attributed to that number.

**Independent Test**: Seed IDs 101 and 202, select each in turn, and verify KPIs, trend, funnel, top products, and top numbers match only the selected ID; then select the prepared zero-result range and verify valid empty states.

### Verification for User Story 1

- [X] T007 [US1] Add exact-number, omitted-filter-resolves-to-All, and zero-result assertions for all six analytics query methods and all five dashboard AJAX actions to `tests/integration/dashboard-number-filter-fixtures.php`, then run them before implementation and record the expected new-behavior failures in `docs/evidence/dashboard-number-filter/fixture-results.md`

### Implementation for User Story 1

- [X] T008 [US1] Apply the exact `number_id = %d` predicate to KPI current values, KPI prior values, trend, funnel, top products, and top numbers in `includes/class-ctc-chat-analytics.php`
- [X] T009 [US1] Pass the resolved exact-number filter into all five dashboard query handlers and append identical `filter_context` data to every successful response in `admin/class-ctc-chat-analytics-admin.php`
- [X] T010 [P] [US1] Render the always-visible native WhatsApp-number select with configured display names and masked final digits plus a persistent polite `aria-live` filter-notice region immediately after the toolbar in `admin/views/dashboard-page.php`
- [X] T011 [US1] Include `number_filter` in the KPI, trend, funnel, top-products, and top-numbers requests and retain the selection across Apply, date changes, and preset buttons in `admin/js/analytics-dashboard.js`
- [X] T012 [P] [US1] Style the filter control and its loading/empty states for the existing dashboard toolbar without horizontal overflow in `admin/css/admin.css`
- [X] T013 [US1] Run the User Story 1 fixture assertions and record commands, environment, per-widget expected/actual totals, and pass/fail results in `docs/evidence/dashboard-number-filter/fixture-results.md`

**Checkpoint**: Filtering by either configured number is complete and independently demonstrable.

---

## Phase 4: User Story 2 - Review All and Unattributed Clicks (Priority: P2)

**Goal**: Administrators can explicitly view all analytics or only truly unattributed rows, while removed historical IDs remain included in All and stale selections recover safely.

**Independent Test**: Verify All matches the legacy totals; Unattributed contains only `NULL`/zero rows; removed ID 303 remains in All but not Unattributed; and an invalid or deleted selected ID falls back once to All with one warning.

### Verification for User Story 2

- [X] T014 [US2] Add fixture assertions for All, Unattributed, removed historical ID 303, and invalid tokens (empty, unknown, removed, zero, negative, decimal, array, and SQL-like input) in `tests/integration/dashboard-number-filter-fixtures.php`, then run them before US2 implementation; require the foundational invalid-token security assertions to pass and record expected failures only for new Unattributed, removed-number, and concurrent-stale behavior

### Implementation for User Story 2

- [X] T015 [US2] Implement All as no number predicate and Unattributed as `(number_id IS NULL OR number_id = 0)` across KPI, trend, funnel, top-products, and top-numbers queries in `includes/class-ctc-chat-analytics.php`
- [X] T016 [P] [US2] Render All numbers and Unattributed options even when zero or one configured number exists, escape display names, and ensure full phone numbers never enter markup in `admin/views/dashboard-page.php`
- [X] T017 [US2] Integrate the foundational resolver across every dashboard handler in `admin/class-ctc-chat-analytics-admin.php`, revalidate each parallel request so concurrently removed selections resolve to All, and propagate one localized non-fatal warning through consistent filter context
- [X] T018 [US2] Add a generation-safe batch coordinator that discards a partially stale five-request batch, selects All, displays one plugin-native warning, performs exactly one complete All retry, and reports ordinary request failures without retry loops in `admin/js/analytics-dashboard.js`
- [X] T019 [P] [US2] Style the accessible live warning region so it remains inside the dashboard container and is not clipped at desktop, 360px width, or 200% zoom in `admin/css/admin.css`
- [X] T020 [US2] Run the User Story 2 fixture assertions and record All/Unattributed/removed-ID totals plus the invalid-token fallback matrix in `docs/evidence/dashboard-number-filter/fixture-results.md`

**Checkpoint**: All, Unattributed, historical removed-number behavior, and stale-selection recovery work independently.

---

## Phase 5: User Story 3 - Compare Like-for-Like Periods (Priority: P3)

**Goal**: Prior-period metrics use the same selected number scope as the current period and remain historically stable after number metadata changes.

**Independent Test**: Enable comparison for number 101, number 202, All, and Unattributed and verify current/prior values use the same scope; rename a number, change the default, and confirm historical attribution and totals do not change.

### Verification for User Story 3

- [X] T021 [US3] Add current/prior scope, comparison-disabled, rename, and default-number-change assertions to `tests/integration/dashboard-number-filter-fixtures.php`, then run them before implementation and record expected new-behavior failures while retaining any already-passing historical-attribution regression assertions

### Implementation for User Story 3

- [X] T022 [US3] Ensure `get_kpis()` forwards the same normalized number filter to both current and prior `get_kpi_values()` calls and preserves stored ID attribution after display-name/default changes in `includes/class-ctc-chat-analytics.php`
- [X] T023 [US3] Preserve the selected number when comparison is toggled or dates are reapplied, and render comparison labels from the filtered KPI response in `admin/js/analytics-dashboard.js`
- [X] T024 [US3] Run the complete fixture matrix, compare analytics row counts and content fingerprints before and after all filter, fallback, rename, default-change, and removal scenarios, and record current/prior totals plus proof of zero deleted, reassigned, or modified analytics records in `docs/evidence/dashboard-number-filter/fixture-results.md`

**Checkpoint**: All three user stories are independently functional and testable.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Complete security, privacy, performance, browser, documentation, and release evidence.

- [X] T025 Verify authorized requests succeed, insufficient capability returns 403, missing/bad nonces are rejected, HTML-like display names are escaped, and full phone numbers are absent from dashboard markup and all five AJAX responses; record results in `docs/evidence/dashboard-number-filter/security-privacy.md`
- [X] T026 Run `EXPLAIN` for filtered queries and 20 warmed five-widget refreshes on a fixture with 100 configured numbers and 100,000 click rows, confirm the `(number_id, clicked_at)` index is used and at least 19 of 20 refreshes complete within two seconds, and record results in `docs/evidence/dashboard-number-filter/performance-results.md`
- [ ] T027 Execute browser QA confirming a fresh dashboard load defaults to All, selection and application require no more than two interactions, the filter works with 0/1/2 configured numbers at desktop, 360px, and 200% zoom, keyboard operation and Apply/preset/comparison retention work, zero results and selected-number deletion recover correctly, throttled A-to-B switching ends on B with one-warning/one-retry behavior, no horizontal overflow occurs, and dashboard JS/CSS assets are absent from unrelated admin screens; record results and screenshot paths under `docs/evidence/dashboard-number-filter/browser-qa.md` and `docs/evidence/dashboard-number-filter/screenshots/`
- [X] T028 [P] Document the dashboard number filter, All/Unattributed semantics, historical attribution behavior, and invalid-selection fallback and add a user-facing changelog/release-note entry in `readme.txt`
- [X] T029 Run PHP syntax checks for changed PHP files and `tests/integration/dashboard-number-filter-fixtures.php`, `node --check admin/js/analytics-dashboard.js`, `composer phpcs`, and `git diff --check`; record exact commands and outcomes in `docs/evidence/dashboard-number-filter/README.md`
- [X] T030 Reconcile implemented behavior with `specs/004-dashboard-number-filter/contracts/dashboard-number-filter.md` and `specs/004-dashboard-number-filter/quickstart.md`, confirm plugin compatibility metadata and minimum PHP/WordPress/WooCommerce versions remain correct, then complete risks, known limitations, evidence links, and release recommendation in `docs/evidence/dashboard-number-filter/README.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Setup)**: Starts immediately.
- **Phase 2 (Foundational)**: Depends on Phase 1 and blocks all user-story work.
- **Phase 3 (US1)**: Depends on Phase 2 and is the MVP.
- **Phase 4 (US2)**: Depends on the shared filter pipeline delivered by US1.
- **Phase 5 (US3)**: Depends on the filter modes delivered by US1 and US2.
- **Phase 6 (Polish)**: Depends on all selected user stories being complete.

### User Story Dependencies

- **US1 (P1)**: Establishes configured-number filtering across all dashboard requests and widgets.
- **US2 (P2)**: Reuses US1's pipeline and adds All, Unattributed, historical-ID, and fallback behavior.
- **US3 (P3)**: Reuses the completed filter modes and applies them consistently to comparison calculations.

### Within Each User Story

- Add fixture assertions before implementation; observe new behavior assertions fail while allowing regression invariants that already hold to pass.
- Complete analytics query behavior before handler and browser integration.
- Complete implementation before recording passing evidence.
- Do not proceed past a checkpoint until that story's independent test passes.

---

## Parallel Opportunities

- T001 and T002 can run in parallel.
- After T003, T004 can run in parallel with T005 and T006 because it changes a separate localization file.
- In US1, T010 and T012 can run in parallel after the filter contract is known.
- In US2, T016 and T019 can run in parallel with backend work.
- T028 can run in parallel with T025-T027 after behavior is final.

## Parallel Example: User Story 1

```text
Task T010: Render the number selector in admin/views/dashboard-page.php
Task T012: Style the dashboard filter in admin/css/admin.css
```

## Parallel Example: User Story 2

```text
Task T016: Render safe All/Unattributed/configured options in admin/views/dashboard-page.php
Task T019: Style the live warning region in admin/css/admin.css
```

---

## Implementation Strategy

### MVP First

1. Complete Phase 1 and Phase 2.
2. Complete US1 through T013.
3. Stop and verify both configured numbers and the zero-result range across every widget.
4. Demo or ship the configured-number filter only if the acceptance evidence passes.

### Incremental Delivery

1. **US1**: Filter the complete dashboard by one configured number.
2. **US2**: Add reliable All/Unattributed analysis and stale-selection recovery.
3. **US3**: Add like-for-like prior-period comparison.
4. **Polish**: Complete security, privacy, performance, responsive browser, standards, and release evidence.

### Scope Guardrails

- Do not add or migrate database columns; use the existing `number_id` and `(number_id, clicked_at)` index.
- Do not change storefront tracking, report-page filtering, CSV behavior, or historical attribution.
- Do not expose full WhatsApp numbers in HTML, JavaScript localization, or AJAX payloads.
- Do not implement partial-widget fallback; any stale selection restarts one complete All batch.

---

## Notes

- `[P]` tasks change different files and have no unmet dependency.
- `[US1]`, `[US2]`, and `[US3]` provide requirement traceability.
- Keep public PHP method additions backward-compatible with optional filter arguments.
- Use prepared SQL for exact IDs and fixed SQL fragments for the Unattributed predicate.
- Preserve unrelated changes in the working tree.
