# Research: Preserve Settings During Plugin Updates

## Decision 1: Treat the issue as a settings-schema migration, not a defaults-merge bug

**Decision**: Explicitly migrate the released prefixed schema to the current canonical schema.

**Rationale**: Git history shows that `main` reads and writes top-level keys such as
`ctc_chat_whatsapp_numbers` and nested keys such as `ctc_chat_number`. Current `develop`
reads `whatsapp_numbers` and `number`. The existing recursive default merge retains the old
keys but does not make current readers use them, so the UI and storefront fall back to
defaults even though legacy values remain stored.

**Alternatives considered**:

- Only improve recursive default merging: rejected because it cannot translate renamed keys.
- Revert all current readers to prefixed keys: rejected because it would discard current
  canonical work and create another compatibility break.
- Read both schemas indefinitely in every consumer: rejected because it duplicates
  precedence logic across admin, public, shortcode, analytics, and link-generation paths.

## Decision 2: Normalize once before settings are cached

**Decision**: Run migration on the early install/upgrade path before plugin runtime objects
load `ctc_chat_settings`.

**Rationale**: Admin, public, shortcode, renderer, analytics, and link-generator classes cache
the option during construction. A later migration would leave the current request using stale
defaults and produce inconsistent behavior.

**Alternatives considered**:

- Migrate only when the settings page opens: rejected because storefront behavior would
  remain broken until an administrator visits the page.
- Migrate lazily in each reader: rejected because it multiplies writes and failure modes.
- Activation-only migration: rejected because ordinary WordPress plugin updates do not
  reliably execute activation hooks.

## Decision 3: Use a distinct settings schema version

**Decision**: Track canonical schema convergence with `ctc_chat_settings_schema_version`,
separate from `ctc_chat_db_version` and the plugin release version.

**Rationale**: The analytics database can already be current while settings remain legacy.
A distinct marker permits independent, idempotent migrations and future schema evolution.

**Alternatives considered**:

- Reuse `ctc_chat_db_version`: rejected because database and settings upgrades have
  independent states.
- Gate only on the plugin version: rejected because retries and partial failures need a
  settings-specific completion marker.
- Detect keys on every request forever: rejected because it adds unnecessary repeated work.

## Decision 4: Preserve canonical values in mixed schemas

**Decision**: When a logical field exists in both schemas, the canonical field wins. Legacy
fields fill only missing canonical fields. Determine existence with `array_key_exists`, so
false, zero, blank strings, nulls, and empty arrays remain authoritative. After the complete
canonical result is constructed and verified in memory, recursively remove recognized mapped
legacy leaf keys as part of the same `ctc_chat_settings` option write. When unknown descendants
exist below a recognized legacy container, retain a pruned shell containing only the structural
ancestors and list indices necessary to keep the unknown paths unchanged. Remove empty shells.

**Rationale**: A mixed record can exist if an administrator saved part of the configuration
after encountering the broken release. Overwriting current canonical choices with stale
legacy values would cause a second data-loss event. Persisting both schemas would duplicate
phone numbers, messages, assignments, and other merchant data indefinitely, contrary to the
project's data-minimization rules. Atomic option persistence provides all-or-nothing behavior:
on failure, WordPress retains the original record and the schema version remains incomplete.

**Alternatives considered**:

- Legacy always wins: rejected because it can undo newer administrator changes.
- Current defaults win: rejected because it reproduces the reported loss for normal legacy
  upgrades.
- Retain both schemas after success: rejected because it creates indefinite duplicate
  merchant data and leaves two competing sources of truth.

## Decision 5: Follow the reference plugin's strict missing-option pattern

**Decision**: Use a strict `false` sentinel to distinguish a missing option from a saved
false-like value, and gate completed migrations by a stored version.

**Rationale**: The user-provided Affiliate Program reference uses
`false === get_option( ..., false )` before adding defaults and separately compares an
installed version before running idempotent upgrades. That pattern correctly preserves an
explicit `"no"` value. Click to Chat needs the same semantics recursively inside its single
nested settings option.

**Alternatives considered**:

- Use `empty()` or truthiness: rejected because valid false, zero, blank, and empty values
  would be overwritten.
- Copy the Affiliate Program update routine unchanged: rejected because it manages flat,
  separate options and does not solve Click to Chat's renamed nested schema.

## Decision 6: Leave malformed settings untouched

**Decision**: If `ctc_chat_settings` exists but is not an array, do not replace it. Record a
non-sensitive migration error marker and leave the schema version incomplete for retry.

**Rationale**: Replacing an unreadable record with defaults would turn a diagnosable problem
into confirmed data loss. A small marker supports troubleshooting without copying phone
numbers, messages, assignments, or other private configuration.

**Alternatives considered**:

- Reset malformed settings to defaults: rejected as destructive.
- Serialize a backup into another option: rejected because it duplicates sensitive settings.
- Fail silently: rejected because support would have no reliable evidence.

## Decision 7: Validate through WordPress integration fixtures without a new test dependency

**Decision**: Add a WP-CLI-compatible integration fixture runner and pair it with syntax,
PHPCS, option comparisons, and browser checks.

**Rationale**: The repository does not currently provide PHPUnit infrastructure. The defect
depends on real option semantics and lifecycle timing, so WordPress-backed fixtures provide
higher confidence without adding a framework in an emergency patch.

**Alternatives considered**:

- Add PHPUnit and a WordPress test suite now: rejected as unnecessary dependency and setup
  scope for the urgent fix.
- Manual testing only: rejected because the schema matrix and idempotence assertions are
  repeatable and high risk.
- Pure PHP tests only: rejected because they cannot prove option writes and hook ordering.

## Decision 8: Verify explicit repository-derived source rows

**Decision**: Treat plugin `1.0.0` at `9a1959e`, plugin `1.0.1` at `4ecc330`, and plugin
`1.0.2` from `62d4ed2` through `3314ec6` as upgrade rows `R1`, `R2`, and `R3`.

**Rationale**: These commits identify the released prefixed schema, the unversioned canonical
schema transition, and the analytics-era schema. Naming them makes the 100% upgrade claim
reproducible while allowing release review to add any distribution build absent from Git.
