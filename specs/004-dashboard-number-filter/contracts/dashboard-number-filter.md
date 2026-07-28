# Contract: Dashboard WhatsApp Number Filter

**Feature**: 004-dashboard-number-filter
**Scope**: Existing authenticated Analytics Dashboard admin AJAX actions

## Compatibility

This contract is additive. Existing clients that omit `number_filter` receive the exact
existing All numbers behavior. Existing action names, date fields, response metrics, reports,
tracking requests, capabilities, and nonce remain unchanged.

All actions require the existing analytics capability (`manage_woocommerce` or
`manage_options`) and valid `ctc_chat_admin_nonce`.

## Shared dashboard request

The following existing actions accept the new field:

- `ctc_analytics_kpis`
- `ctc_analytics_trend`
- `ctc_analytics_funnel`
- `ctc_analytics_top_products`
- `ctc_analytics_top_numbers`

| Parameter | Type | Required | Contract |
|-----------|------|----------|----------|
| `number_filter` | string | no | `all`, `unattributed`, or positive decimal configured number ID; default `all` |

Existing `start_date`, `end_date`, `compare`, and action-specific parameters retain their
current contracts.

### Accepted examples

```text
number_filter=all
number_filter=unattributed
number_filter=7
```

### Invalid examples

```text
number_filter=0
number_filter=-1
number_filter=7.0
number_filter=id:7
number_filter=999999        # not a current configured ID
number_filter[]=7
number_filter=7%20OR%201=1
```

Invalid, malformed, stale, and removed selections do not cause a partial filtered response.
They resolve to All and signal fallback metadata.

## Server normalization

| Requested value | Resolved mode | Query semantics |
|-----------------|---------------|-----------------|
| omitted / `all` | `all` | Date range only; no number predicate |
| `unattributed` | `unattributed` | `number_id IS NULL OR number_id = 0` |
| valid current positive ID | `number` | Prepared equality on that ID |
| anything else | `all` with fallback | Date range only; localized notice metadata |

A positive ID is valid only when it exists in the current normalized configured-number list.
Removed positive IDs remain included under All but are not accepted as individual filters.

## Additive response metadata

Each successful dashboard response adds `filter_context` at the top level of its existing
`data` payload:

```json
{
  "filter_context": {
    "requested": "7",
    "resolved": "7",
    "mode": "number",
    "fell_back": false,
    "message": ""
  }
}
```

Invalid or stale selection example:

```json
{
  "filter_context": {
    "requested": "invalid",
    "resolved": "all",
    "mode": "all",
    "fell_back": true,
    "message": "The selected WhatsApp number is no longer available. Showing all numbers."
  }
}
```

Rules:

- `requested` must be sanitized and bounded; it must not echo arbitrary raw payload data.
- `message` is localized, contains no phone value or raw input, and is empty without fallback.
- Existing metric/widget fields retain their names and types.
- Valid zero-result scopes return success with existing zero/empty/unavailable representations.

## Dashboard batch behavior

1. The page snapshots one selector value when a refresh begins.
2. All five requests send that same value and current request-generation ID is retained in
   page state.
3. Responses from older generations are ignored.
4. If every response reports the requested resolved scope, each section renders normally.
5. If any current-generation response reports `fell_back=true`, the page:
   - prevents that generation from becoming the final mixed dashboard;
   - changes the selector to All;
   - announces one localized warning in the dashboard notice region;
   - issues one complete All refresh;
   - does not retry again for that recovery generation.
6. A failed request shows the existing section error state; no section remains indefinitely
   in Loading state.

## Widget semantics

| Scope | KPI/trend/funnel/products | Top WhatsApp Numbers |
|-------|---------------------------|----------------------|
| All | All retained clicks in range | Existing ranking, including Unattributed aggregate |
| Exact number | Only matching stable ID | At most the selected number row |
| Unattributed | Only null/zero IDs | At most the Unattributed row |

Prior-period comparison uses the exact same resolved scope as the current period. Stored
`is_unique` values are summed as today; filtering does not recompute uniqueness.

## UI contract

- A native selector labeled “WhatsApp number” is always rendered on the Dashboard.
- Option order: All numbers; configured numbers in settings order; Unattributed.
- Configured labels contain escaped administrator display name plus masked digits only; full
  phone values are absent from visible text, markup data, and localized client configuration.
- All is selected on fresh load. Selection is page-session state only.
- Selector changes are staged until Apply. Date presets immediately refresh with the current
  staged number; custom dates and comparison retain it.
- Invalid/removed fallback uses the plugin-native warning banner in a polite live region,
  outside the branded header and without the WordPress `.notice` class.
- The toolbar remains keyboard-operable and avoids horizontal page scrolling at 360px width
  and 200% zoom.

## Error and security behavior

| Condition | Required result |
|-----------|-----------------|
| Missing/invalid capability | Existing 403 behavior |
| Missing/invalid nonce | Existing request rejection behavior |
| Invalid date range | Existing 400 behavior |
| Invalid/stale `number_filter` | 200 success using All + fallback metadata and warning |
| Valid scope with zero rows | 200 success with zero/empty/unavailable widget states |
| Unexpected transport/server failure | Section error state; no indefinite loader |

All SQL remains prepared and all UI output is escaped for context.