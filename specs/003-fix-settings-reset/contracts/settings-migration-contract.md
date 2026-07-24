# Contract: Click to Chat Settings Migration

## Purpose

Define the compatibility contract for normalizing `ctc_chat_settings` during install,
plugin update, activation, retry, and subsequent requests.

## Inputs

- The raw `ctc_chat_settings` option, using a strict missing-option sentinel.
- Current canonical defaults.
- Current target settings schema version `1.0.0`.
- Existing settings schema version, if present.

## Output

One of:

1. **Success, changed**: Persist the normalized settings, then persist the target schema
   version and clear any prior migration error.
2. **Success, unchanged**: Do not rewrite settings; ensure the schema version is current and
   clear any prior migration error.
3. **Failure, unsafe input**: Do not modify settings or advance the schema version; persist
   only a non-sensitive migration error marker.

## Precedence Rules

1. A canonical field that exists is authoritative, including false, zero, blank, null, or an
   empty collection.
2. A mapped legacy field supplies a canonical value only when the canonical field is absent.
3. A current default supplies a canonical value only when neither canonical nor mapped legacy
   data supplies that field.
4. Unknown top-level and nested fields are carried forward unchanged.
5. After canonical values and defaults are complete in memory, recognized mapped legacy
   fields are removed from the candidate record. If a recognized legacy container contains
   unknown children, retain its shell with only those children. Unknown and extension-owned
   key paths and values remain unchanged.
6. The normalized option is persisted once, so a failed write leaves the original legacy or
   mixed record intact and a successful write contains one copy of each recognized setting.

## Collection Rules

- Preserve WhatsApp number ordering.
- Map every number independently; do not drop incomplete records during migration.
- Preserve number IDs because product meta may refer to them.
- Preserve assignment and exclusion list ordering and duplicates as stored; validation or
  cleanup is outside this migration.
- Preserve multiline template content without re-sanitizing it during migration.

## Lifecycle Rules

- Migration MUST run before plugin runtime classes cache the option.
- Normal plugin update MUST trigger migration without requiring activation or an admin-page
  visit.
- Activation MUST use the same migration entry point and MUST NOT write a stale pre-migration
  snapshot afterward.
- Database migration success MUST NOT be treated as settings migration success.
- Repeated calls MUST be safe and converge to a no-write fast path.

## Failure Rules

- A missing option is a fresh-install case, not an error.
- A non-array existing option is malformed and MUST remain untouched.
- A persistence failure MUST leave the schema version unchanged so a later request retries.
- Diagnostic data MUST exclude the settings payload and all merchant-identifying values.

## Compatibility Assertions

- The option name remains `ctc_chat_settings`.
- Existing product meta keys remain unchanged.
- Recognized released prefixed paths are replaced by their documented canonical paths only
  through the successful atomic option write; unknown persisted paths remain unchanged.
- No shortcode, admin route, public selector, WhatsApp URL, or button-rendering contract
  changes.
- No analytics behavior changes except availability of absent analytics defaults.

## Verification Matrix

| Fixture | Required result |
|---------|-----------------|
| Full released prefixed schema | All logical values appear canonically; mapped fields removed and unknown container shells retained |
| Canonical schema | Existing values unchanged; absent defaults added |
| Mixed schema | Canonical wins conflicts; legacy fills gaps; mapped legacy paths removed |
| Explicit false/zero/blank/null/empty | Value remains authoritative, including canonical-only records |
| Partial analytics | Existing fields retained; only missing fields defaulted |
| Unknown top-level/nested fields | Retained unchanged, including under pruned legacy containers |
| Missing option | Complete fresh defaults created |
| Malformed option | Original untouched; diagnostic recorded |
| Three consecutive runs | Runs 2 and 3 perform no settings write |
| Simulated failed write | Original record and incomplete schema marker remain available for retry |
