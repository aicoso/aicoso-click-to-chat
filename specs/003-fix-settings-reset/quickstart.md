# Quickstart: Validate Settings Preservation

## Prerequisites

- A disposable WordPress site with WooCommerce active.
- The plugin checked out on `003-fix-settings-reset`.
- WP-CLI access to the disposable site.
- A database backup before exercising a real legacy installation.

Do not run destructive fixtures against a production store.

## Required references

- [Feature specification](spec.md)
- [Data model and field map](data-model.md)
- [Migration contract](contracts/settings-migration-contract.md)

## 1. Static checks

From the plugin directory:

```powershell
php -l aicoso-click-to-chat.php
php -l includes/class-ctc-chat-install.php
php -l includes/class-ctc-chat-settings-migrator.php
composer phpcs
```

Expected: every syntax command and PHPCS completes successfully.

## 2. Run the integration fixture matrix

The implementation phase adds a WP-CLI-compatible fixture runner:

```powershell
wp --path="<wordpress-root>" eval-file `
  "wp-content/plugins/aicoso-click-to-chat/tests/integration/settings-migration-fixtures.php"
```

Expected fixture results:

- released prefixed configuration maps completely;
- canonical configuration is not overwritten;
- mixed configuration uses canonical precedence;
- recognized mapped legacy paths are absent after successful persistence;
- explicit false, zero, blank, and empty values survive;
- missing analytics fields receive defaults;
- unknown keys survive;
- fresh install receives complete defaults;
- malformed configuration remains untouched and records a safe error marker;
- the second and third migration runs do not write settings.

Save the output in
`docs/evidence/settings-update-preservation/fixture-results.md`.

## 3. Validate the supported upgrade matrix

Run the real upgrade procedure on disposable sites for each matrix row:

- `R1`: plugin 1.0.0 at `9a1959e`, released prefixed schema;
- `R2`: plugin 1.0.1 at `4ecc330`, canonical or mixed unversioned schema;
- `R3`: plugin 1.0.2 across analytics commits `62d4ed2` through `3314ec6`, canonical or
  mixed schema with absent, partial, and customized analytics.

Record the source row and version in the evidence package. Also run the missing-option,
malformed-option, and failed-write state-safety fixtures.

For each row, use a disposable site:

1. Configure at least two WhatsApp numbers with different assignments.
2. Customize button text, icon, colors, and all available placements.
3. Customize every message template.
4. Configure pages, posts, categories, tags, and product exclusions.
5. Configure advanced/catalog behavior.
6. Capture the original option:

```powershell
wp --path="<wordpress-root>" option get ctc_chat_settings --format=json
```

7. Update the plugin files without deactivating or reactivating it.
8. Load one storefront page and one plugin admin page to exercise the normal update path.
9. Capture the canonical option and schema marker:

```powershell
wp --path="<wordpress-root>" option get ctc_chat_settings --format=json
wp --path="<wordpress-root>" option get ctc_chat_settings_schema_version
```

Expected:

- every logical legacy value is present under its canonical field;
- recognized mapped legacy paths are removed and unknown paths remain byte-for-byte equal;
- analytics defaults exist;
- no unrelated value changes;
- the schema marker reports target version `1.0.0`.

## 4. Verify repeated activation and update checks

Run the normal lifecycle more than once:

```powershell
wp --path="<wordpress-root>" plugin deactivate aicoso-click-to-chat
wp --path="<wordpress-root>" plugin activate aicoso-click-to-chat
wp --path="<wordpress-root>" plugin deactivate aicoso-click-to-chat
wp --path="<wordpress-root>" plugin activate aicoso-click-to-chat
```

Capture `ctc_chat_settings` after each activation. Expected: all captures after the first
successful migration are identical.

## 5. Browser regression routes

Verify on desktop and a narrow viewport:

| Route / journey | Expected |
|-----------------|----------|
| Click to Chat settings | Saved enablement, appearance, placement, exclusions, and advanced values display |
| WhatsApp numbers | All labels, phone values, defaults, and assignments display |
| Message templates | Every customized template displays unchanged |
| Product page | Button visibility, appearance, number, and prepared message match before update |
| Shop/archive | Placement, exclusions, number, and message match before update |
| Cart and checkout | Visibility, position, number, and cart message match before update |
| Thank-you page | Visibility, number, and order message match before update |
| Shortcode button | Appearance, number, and message match before update |
| Floating button | Visibility, position, appearance, number, and message match before update |

Record results in `docs/evidence/settings-update-preservation/browser-qa.md`.

## 6. Malformed-option safety

On a disposable site only, seed a non-array `ctc_chat_settings` value and exercise the update
path.

Expected:

- the original value remains unchanged;
- `ctc_chat_settings_schema_version` does not advance;
- `ctc_chat_settings_migration_error` contains only the documented diagnostic fields;
- no phone number, message, assignment, URL, or settings payload appears in diagnostics.

## Release gate

Release only when static checks, all fixture cases, the real upgrade path, repeated lifecycle
checks, and all affected browser journeys pass. The evidence index must state the tested
source version, target version, environment, results, unresolved risks, and release
recommendation.
