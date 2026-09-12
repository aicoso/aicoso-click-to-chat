# Research: Dashboard WhatsApp Number Filter

**Feature**: 004-dashboard-number-filter
**Date**: 2026-07-28

## Decision 1: Use a dashboard-only canonical scope token

**Decision**: Send `number_filter` with each dashboard request. Accepted values are `all`,
`unattributed`, or a positive decimal configured number ID represented as a string. Omission
is backward-compatible and resolves to `all`.

**Rationale**: The three modes are unambiguous while keeping stable number identity separate
from mutable names and private phone digits. A dashboard-only field avoids changing reports,
where the existing `number_id=0` behavior means no filter.

**Alternatives considered**:
- Reuse report `number_id`: rejected because zero/empty cannot distinguish All from
  Unattributed.
- Prefix IDs as `id:7`: valid but adds parsing complexity without resolving an ambiguity that
  strict numeric validation does not already solve.
- Filter by name or phone: rejected because values are mutable and phone data is private.

## Decision 2: Always render a native selector

**Decision**: Render a visible, labeled native selector on first paint with All numbers,
current normalized configured numbers in settings order, and Unattributed. Show configured
options as administrator display name plus masked digits, optionally identifying the default.

**Rationale**: Native controls provide keyboard operation, screen-reader labeling, type-ahead,
and reliable behavior for up to 100 options without a dependency. Always-visible All and
Unattributed options keep the layout stable and let stores with zero or one current number
reconcile historical data.

**Alternatives considered**:
- Show only when multiple numbers exist: rejected by clarification and blocks Unattributed
  reconciliation.
- Select2/custom combobox: rejected because the scale does not justify extra lifecycle and
  accessibility complexity.
- Full phone display: rejected by privacy requirements.

## Decision 3: Validate centrally and fall back to All

**Decision**: At the capability/nonce-protected admin request boundary, strictly recognize the
complete token and validate positive IDs against normalized current settings. Invalid,
tampered, stale, or removed IDs resolve to All. Every response includes non-sensitive
`filter_context` metadata with requested/resolved scope, mode, fallback flag, and localized
message.

**Rationale**: One boundary keeps all five endpoints consistent and prevents arbitrary
historical-ID probing. The clarified All fallback is usable and never presents partial data as
the selected number.

**Alternatives considered**:
- HTTP 400 and no refresh: rejected by the clarified UX.
- Empty result for invalid scope: rejected because it falsely implies the number has zero
  activity.
- Accept any positive historical ID: rejected because removed-number selection is explicitly
  outside V1 scope.

## Decision 4: Retry one complete batch after any fallback

**Decision**: Snapshot scope once per dashboard refresh. If any current-generation endpoint
reports fallback, ignore that batch, set the selector to All, show one accessible warning,
and issue one fresh All batch. Limit the recovery to one retry.

**Rationale**: Five independent requests can validate at slightly different moments if a
number is concurrently removed. A complete retry guarantees all widgets describe one scope.
The existing generation guard prevents older responses from painting after the retry.

**Alternatives considered**:
- Let each section render its independently resolved result: rejected because it can mix
  number-specific and All results.
- Combine all widgets into one endpoint: rejected because Analytics V1 intentionally uses
  independent loaders and failure isolation.
- Display only the KPI fallback response: rejected as insufficient when another request was
  evaluated before removal.

## Decision 5: Reuse one prepared SQL filter builder

**Decision**: Extend the existing analytics filter builder so an internal exact-number filter
adds `number_id = %d`, Unattributed adds `(number_id IS NULL OR number_id = 0)`, and All adds
no clause. Reuse it across KPI totals/top placement, trend, funnel, top products, and top
numbers. Append optional filter arguments to service method signatures.

**Rationale**: Centralized SQL semantics prevent widget and comparison drift. Existing callers
remain compatible because the new arguments default to All. Prepared placeholders preserve
query safety.

**Alternatives considered**:
- Duplicate a number clause in every method: rejected as mismatch-prone.
- Use `COALESCE(number_id, 0) = 0`: rejected because the explicit null/zero predicate is more
  transparent and friendlier to index planning.
- Add aggregate storage: rejected as unnecessary at the stated scale.

## Decision 6: Preserve stored uniqueness and historical attribution

**Decision**: Filter stored click rows by stable `number_id`; do not recompute `is_unique`.
Renames/default changes affect current labels only. Removed positive IDs remain attributed and
included under All, but are not individually selectable in V1. Unattributed means only null or
zero number identity.

**Rationale**: This matches the existing analytics model and avoids rewriting history. A
removed number is unknown to current settings but is not the same as an unattributed click.

**Alternatives considered**:
- Reassign history when default changes: rejected as analytically false.
- Treat removed IDs as Unattributed: rejected because an identity was recorded.
- Snapshot phone/name per click: rejected because filtering needs no migration or duplicated
  personal data.

## Decision 7: Keep existing Apply and section-loading behavior

**Decision**: Selecting a number stages the scope; Apply refreshes all widgets. Date presets
retain and immediately apply the staged number. Custom date/comparison behavior remains
unchanged. Add explicit failed-request states while retaining the current generation guard.

**Rationale**: This is consistent with the toolbar's existing interaction and avoids request
churn. Failure handlers prevent indefinite loading, while the generation guard provides
correct last-request-wins behavior.

**Alternatives considered**:
- Auto-refresh on selector change: rejected as inconsistent with custom filters and creates
  extra parallel request batches.
- Persist per-user choice: rejected because the specification limits state to the page
  session.

## Decision 8: No database or storefront change

**Decision**: Use the existing nullable `number_id`, `clicked_at`, and composite
`(number_id, clicked_at)` index. Do not change tracking, settings schema, retention, uninstall,
exports, reports, or storefront assets.

**Rationale**: Tracking already stores stable positive IDs or null, and the exact-number query
pattern is indexed. All continues to use the date index. This confines performance and risk to
the admin dashboard.

**Alternatives considered**:
- Add a new index/schema version: rejected until `EXPLAIN` and 100,000-row evidence demonstrate
  a need.
- Change tracking payload: rejected because current attribution is sufficient.

## Decision 9: Verify with a deterministic three-scope fixture matrix

**Decision**: Add a WP-CLI integration fixture that safely seeds two configured IDs, one
removed historical ID, and null/zero attribution across current and prior periods, products,
placements, cart totals, devices, and uniqueness flags. Verify every dashboard query under
All, each configured number, Unattributed, zero-result, and stale fallback scenarios; restore
settings and delete fixture rows afterward.

**Rationale**: A controlled matrix proves cross-widget consistency, comparison correctness,
All regression behavior, and data preservation. Browser and performance evidence cover the
parts a query fixture cannot.

**Alternatives considered**:
- Manual QA only: rejected because arithmetic regressions across five query families are easy
  to miss.
- Production-like copied data: rejected for privacy and reproducibility reasons.
## Decision 10: Use layered security, performance, and browser release gates

**Decision**: Verify capability and nonce rejection, escaped hostile display names, zero raw
phone occurrences in rendered HTML and all five response payloads, deterministic data
preservation, representative `EXPLAIN` output, 20 warmed complete-dashboard refreshes at the
specified scale, and keyboard/responsive/stale-response browser scenarios. Store results in
a dedicated evidence package.

**Rationale**: Query assertions alone do not prove privacy, request safety, responsive UX,
last-request-wins behavior, or the user-facing two-second performance target. Nineteen of 20
warmed refreshes meeting the target is a reproducible p95 gate.

**Alternatives considered**:
- One stopwatch run: rejected as too noisy.
- Visual masking check only: rejected because full phones could leak in markup or responses.
- Desktop mouse-only QA: rejected because the specification requires keyboard, zoom, and
  narrow-viewport behavior.
