# Data Model: Dashboard WhatsApp Number Filter

**Feature**: 004-dashboard-number-filter
**Date**: 2026-07-28

## Storage impact

No storage entity or schema changes. The feature reads existing click and configured-number
records and adds transient dashboard filter state only.

| Existing store | Fields used | Change |
|----------------|-------------|--------|
| `{prefix}ctc_chat_clicks` | `number_id`, `clicked_at`, existing metric dimensions | None |
| `ctc_chat_settings['whatsapp_numbers']` | `id`, `name`, `number`, `is_default` | None |
| Browser page state | selected number scope for current dashboard session | New transient state; not persisted |

The existing composite index `number_clicked_at (number_id, clicked_at)` supports exact-number
and unattributed date-range queries. The existing `clicked_at` index supports All numbers.

## Entity: Configured WhatsApp Number

Represents one current number available to the administrator.

| Attribute | Rules | Dashboard use |
|-----------|-------|---------------|
| `id` | Positive unique stable integer after existing normalization | Filter identity and option value |
| `name` | Administrator-provided display name | Visible option label after escaping |
| `number` | Full configured phone value | Never sent or displayed in full; used only to produce a mask |
| `is_default` | At most one current record is default | Optional localized option annotation only |
| settings order | Existing array order | Selector order |

### Identity rules

- Number identity is the stable positive `id`, never the name, phone, position, or default flag.
- Renaming a number or changing the default does not change historical click association.
- A removed positive ID remains an attributed historical identity in click data but is not a
  selectable current number in this feature version.

## Entity: Analytics Click Record

Existing append-only historical click used for aggregation.

| Attribution state | Stored representation | Filter membership |
|-------------------|-----------------------|------------------|
| Current configured number | Positive `number_id` present in current settings | All + that exact number |
| Removed historical number | Positive `number_id` absent from current settings | All only |
| Unattributed | `number_id IS NULL` or legacy/invalid `number_id = 0` | All + Unattributed |

Filtering does not change `is_unique`, attribution, timestamps, cart values, products,
placements, device type, or any retained record.

## Entity: WhatsApp Number Selection

Transient state representing the administrator's requested dashboard scope.

| Attribute | Values | Validation |
|-----------|--------|------------|
| `requested` | `all`, `unattributed`, positive decimal ID string, or invalid input | Preserve only as sanitized non-sensitive response context |
| `resolved` | `all`, `unattributed`, or positive decimal current ID string | Must be canonical |
| `mode` | `all`, `unattributed`, `number` | Derived server-side |
| `number_id` | Positive integer for `number`; absent otherwise | Must exist in normalized current settings |
| `fell_back` | Boolean | True only when requested value cannot be safely applied |
| `message` | Localized fallback message or empty | Must contain no phone number or raw request data |

### Canonical transitions

```text
Page load
  -> requested/resolved All

User stages selector value
  -> Apply or date preset
  -> validating
     -> valid All          -> resolved All
     -> valid Unattributed -> resolved Unattributed
     -> valid current ID   -> resolved Number(ID)
     -> invalid/stale ID   -> resolved All + fell_back + notice
```

Selection is retained only in the current page state. It is not saved to user metadata,
settings, cookies, local storage, or URLs in V1.

## Entity: Dashboard Request Context

Snapshot shared by one logical refresh batch.

| Attribute | Rules |
|-----------|-------|
| start/end date | Existing inclusive validated dashboard range |
| comparison | Existing boolean behavior |
| number filter | One selector snapshot reused for all five requests |
| generation | Monotonically increasing page-local request generation |
| fallback retry used | Boolean; prevents recovery loops |

A request batch contains KPI, trend, funnel, top-products, and top-numbers requests. All must
resolve to one number scope. An older generation cannot update the current page.

## Entity: Dashboard Result Set

Existing widget payload plus additive `filter_context` metadata.

| Result family | Number filtering requirement |
|---------------|------------------------------|
| KPI totals | Apply to totals and top-placement query |
| KPI comparison | Apply identical scope to current and prior periods |
| Click trend | Apply before site-timezone bucketing; preserve empty buckets |
| Intent funnel | Apply before placement grouping |
| Top products | Apply before product grouping and ranking |
| Top numbers | All ranks numbers; exact/Unattributed returns at most the matching row |

### Batch consistency invariant

For a rendered generation, every result must report the same resolved scope. If any request
falls back because the number was removed or invalidated during the batch, none of that
batch's widget results is final. The page switches to All, announces one warning, and retries
one complete batch. The retry must not recursively retry.

## Validation rules

1. Omitted or exact `all` resolves to All.
2. Exact `unattributed` resolves to Unattributed.
3. An ASCII decimal string representing an integer greater than zero resolves to Number only
   when that ID exists in normalized current settings.
4. Zero, negative, decimal, array/object, mixed text, unknown ID, removed ID, and SQL-like
   values fall back to All.
5. Exact-number query predicate is prepared `number_id = %d`.
6. Unattributed predicate is `(number_id IS NULL OR number_id = 0)`.
7. All adds no number predicate and therefore includes current, removed, null, and zero IDs.
8. User-visible labels and messages are translated and escaped; visible number text is masked.

## Data lifecycle

- No click or option writes occur when rendering, selecting, applying, validating, or querying
  this filter.
- Existing retention, export, uninstall, and deletion behavior is unchanged.
- Integration fixtures snapshot settings, tag inserted rows, restore settings, and delete only
  tagged rows even after assertion failure.