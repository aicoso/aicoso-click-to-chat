# Implementation Plan: Preserve Settings During Plugin Updates

**Branch**: `003-fix-settings-reset` | **Date**: 2026-07-23 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/003-fix-settings-reset/spec.md`

## Summary

Prevent update-time configuration loss by introducing an explicit, versioned settings-schema
migration before any runtime class caches `ctc_chat_settings`. The migration atomically
converts the released prefixed schema (`ctc_chat_*` keys) into canonical schema `1.0.0`,
preserves canonical and unknown values, removes recognized legacy copies after successful
mapping, adds only genuinely absent defaults such as analytics fields, and leaves malformed
data untouched with a non-sensitive diagnostic marker.

Repository history confirms that commit `4ecc330` replaced the released prefixed settings
readers with unprefixed readers without a migration. The Affiliate Program reference confirms
the safe baseline pattern of testing option existence with a strict `false` sentinel and
running version-gated, idempotent upgrades; its flat-option approach is adapted here for the
nested Click to Chat settings array.

## Technical Context

**Language/Version**: PHP 7.4+ using WordPress-compatible syntax

**Primary Dependencies**: WordPress Options API and plugin lifecycle hooks; existing
WooCommerce integration; no new runtime dependency

**Storage**: Existing `ctc_chat_settings` option; new scalar
`ctc_chat_settings_schema_version` option; non-sensitive
`ctc_chat_settings_migration_error` marker only when migration cannot safely proceed

**Testing**: A WP-CLI integration fixture matrix, repeat-run assertions, PHP syntax checks,
repository PHPCS, and browser verification of affected admin and storefront routes

**Target Platform**: WordPress 6.2+ and WooCommerce 8.2+ stores updating from supported
Click to Chat releases; PHP 7.4+

**Project Type**: WordPress/WooCommerce extension plugin

**Performance Goals**: Complete migration within 5 seconds for 100 WhatsApp numbers and
500 exclusions; perform no settings write after the configuration has converged

**Constraints**: Preserve false, zero, blank, null, empty, unknown, and canonical values;
persist only one canonical copy of each recognized setting; initialize analytics defaults
only when absent;
run before plugin objects cache settings; do not change storefront contracts or product meta

**Scale/Scope**: One nested settings option, twelve legacy top-level groups, nested number
and assignment records, message templates, exclusions, advanced settings, analytics defaults,
fresh installs, mixed schemas, repeated runs, and malformed-option handling

## Supported Upgrade Matrix

| Row | Source evidence | Source settings schema | Required verification |
|-----|-----------------|------------------------|-----------------------|
| `R1` | Plugin `1.0.0`, `main` at `9a1959e` | Released prefixed schema; analytics absent | Full legacy mapping, pruned-shell preservation, defaults, storefront comparison |
| `R2` | Plugin `1.0.1`, schema change at `4ecc330` | Canonical or mixed, unversioned; analytics absent | Canonical precedence, legacy gap fill, pruned-shell preservation, defaults |
| `R3` | Plugin `1.0.2`, analytics change at `62d4ed2` through `3314ec6` | Canonical or mixed, unversioned; analytics absent, partial, or customized | Analytics preservation/default completion, mixed precedence, idempotence |

Missing-option, malformed-option, and failed-write fixtures are mandatory state-safety rows
in addition to `R1`-`R3`. If release records identify another directly upgradable build
before release, extend this matrix and its fixtures.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Backward-Compatible Store Contracts — PASS**: The plan keeps the option name, logical
  values, unknown and extension-owned keys, product meta, shortcodes, routes, selectors, and
  storefront behavior. Recognized released `ctc_chat_*` paths are internal schema aliases,
  atomically replaced by documented canonical equivalents under explicit compatibility
  handling, migration evidence, release notes, and the appropriate patch-release bump.
- **WordPress/WooCommerce-Native Architecture — PASS**: The design uses existing lifecycle
  and option conventions, stays within the plugin bootstrap/install structure, and introduces
  no dependency.
- **Security, Privacy, and Data Integrity — PASS**: The settings payload is never logged or
  exported by migration, and successful persistence contains one copy of each recognized
  logical setting. Malformed input is left unchanged; diagnostics contain only an error code,
  source schema, target schema, and timestamp.
- **Incremental and Verifiable Delivery — PASS**: Pure mapping behavior, option integration,
  idempotence, fresh install, malformed input, and storefront continuity each have explicit
  fixture or browser checks.
- **Evidence-Based Releases — PASS**: Evidence will be stored under
  `docs/evidence/settings-update-preservation/` with fixture results, option comparisons,
  affected-route checks, and a release recommendation.

**Post-design re-check**: PASS. The data model and migration contract define deterministic
precedence, failure behavior, write conditions, and validation coverage. No exception or
complexity waiver is required.

## Project Structure

### Documentation (this feature)

```text
specs/003-fix-settings-reset/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── checklists/
│   └── requirements.md
└── contracts/
    └── settings-migration-contract.md
```

### Source Code (repository root)

```text
aicoso-click-to-chat.php
includes/
├── class-ctc-chat-install.php
└── class-ctc-chat-settings-migrator.php
tests/
└── integration/
    └── settings-migration-fixtures.php
docs/
└── evidence/
    └── settings-update-preservation/
        ├── README.md
        ├── fixture-results.md
        └── browser-qa.md
```

**Structure Decision**: Add one focused migrator class under the existing `includes/`
boundary. Keep lifecycle orchestration in `CTC_Chat_Install`, reduce the activation callback
to that single normalization path, and add a WP-CLI-compatible integration fixture runner.
No admin, public, or persistence layer is reorganized.

## Implementation Strategy

1. Load the migrator before `CTC_Chat_Install::init()` so the early `plugins_loaded` upgrade
   path can normalize settings before runtime classes read them.
2. Make one migration entry point responsible for fresh-install defaults, legacy mapping,
   canonical default completion, schema-version updates, and failure diagnostics.
3. Invoke settings migration on every early upgrade check, independently of the analytics
   database version. Keep the fast path read-only when schema and defaults are current.
4. Change activation orchestration so it does not cache the pre-migration option and write it
   back after migration. Database setup and settings normalization must each run exactly once
   through `CTC_Chat_Install`.
5. Map legacy fields recursively according to
   [settings-migration-contract.md](contracts/settings-migration-contract.md). Canonical
   fields win when both schemas contain the same logical field; legacy fields fill only
   absent canonical fields. Strict key existence, not truthiness, determines absence.
6. Merge current defaults into the canonical result without filtering unknown keys. After
   verifying the complete in-memory result, remove only recognized mapped legacy fields in
   the same atomic option write. If a recognized legacy container still contains unknown
   children, retain that container shell with only those unknown children; never remove
   unknown or extension-owned fields.
7. Update the option only when the normalized value differs. Set the schema version only
   after the settings write succeeds. On malformed input, leave settings and schema version
   unchanged and write only the diagnostic marker.
8. Validate the fixture matrix and storefront/admin behavior before release, then update
   plugin version metadata and release notes as a patch release.

The initial target settings schema version is `1.0.0`. Fixture comparisons use a logical
projection: mapped before/after paths must have identical values, unknown paths must remain
exactly unchanged, and only recognized path canonicalization, absent defaults, and schema
metadata are permitted differences.

## Complexity Tracking

No constitution violations require justification.
