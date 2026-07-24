# Feature Specification: Preserve Settings During Plugin Updates

**Feature Branch**: `003-fix-settings-reset`

**Created**: 2026-07-23

**Status**: Draft

**Input**: User description: "Plugin updates reset all existing Click to Chat settings.
Preserve every live configuration and initialize only settings introduced by the update."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Upgrade Without Configuration Loss (Priority: P1)

As a store administrator with a configured Click to Chat installation, I can update the
plugin without any saved configuration changing, so the live store continues operating
exactly as it did before the update.

**Why this priority**: Losing phone numbers, display rules, or button configuration can
immediately break a live customer contact journey and force the merchant to rebuild the
plugin configuration from memory.

**Independent Test**: Configure every existing settings category with non-default values,
perform an update, and compare the complete configuration and representative storefront
buttons before and after the update.

**Acceptance Scenarios**:

1. **Given** an older installation with customized WhatsApp numbers, assignments, button
   appearance, messages, placements, exclusions, and advanced settings, **When** the plugin
   is updated, **Then** every saved value remains unchanged.
2. **Given** a live store with Click to Chat enabled or deliberately disabled, **When** the
   plugin is updated, **Then** the enabled state and resulting storefront visibility remain
   unchanged.
3. **Given** saved values that are intentionally false, zero, blank, or empty collections,
   **When** the plugin is updated, **Then** those values are preserved and are not treated as
   missing.

---

### User Story 2 - Initialize Only Newly Introduced Settings (Priority: P2)

As an existing store administrator, I receive valid defaults for settings introduced by the
new version without those defaults replacing any part of my existing configuration.

**Why this priority**: New analytics settings must be usable after the update, but their
introduction cannot justify replacing unrelated live configuration.

**Independent Test**: Update representative legacy configurations that have no analytics
settings, complete analytics settings, and partially populated analytics settings; verify
that only absent fields receive defaults.

**Acceptance Scenarios**:

1. **Given** an existing configuration with no analytics settings, **When** the plugin is
   updated, **Then** the new analytics settings receive their documented defaults and all
   pre-existing settings remain byte-for-byte equivalent in meaning.
2. **Given** an existing configuration with customized analytics settings, **When** the
   plugin is updated, **Then** each customized analytics value is preserved.
3. **Given** an analytics configuration containing some but not all current fields,
   **When** the plugin is updated, **Then** only missing analytics fields receive defaults.

---

### User Story 3 - Safe and Repeatable Updates (Priority: P3)

As a store administrator or support engineer, I can apply the same update path more than
once without settings drifting, duplicating, or reverting.

**Why this priority**: Update routines may run again after reactivation, retry, deployment,
or maintenance. Repetition must never become a second opportunity for data loss.

**Independent Test**: Run the update process repeatedly against the same configured store
and confirm that the configuration after the first successful update remains unchanged on
every subsequent run.

**Acceptance Scenarios**:

1. **Given** a successfully updated configuration, **When** the update process runs again,
   **Then** no existing or newly initialized value changes.
2. **Given** saved keys from an older release or another compatible extension that are not
   recognized by the current defaults, **When** the plugin is updated, **Then** those keys
   and values remain intact.
3. **Given** a mixed record where the same logical setting exists in legacy and canonical
   form, **When** the plugin is updated, **Then** the canonical value remains authoritative,
   missing canonical values are supplied from legacy values, and recognized legacy copies
   are removed only as part of the successful atomic settings write.
4. **Given** an update that is interrupted and later retried, **When** the retry completes,
   **Then** the last valid saved configuration is preserved and no duplicate configuration
   entries are created.

### Edge Cases

- The saved configuration contains explicit `false`, `0`, an empty string, or an empty list.
- A nested settings group exists but contains only some fields known to the new version.
- The installation has multiple WhatsApp numbers with default-number and product, category,
  or page assignments.
- Templates contain multiline text, placeholders, Unicode, quotes, or intentionally blank
  content.
- Exclusion lists are empty, large, or reference content that no longer exists.
- Legacy, layout, widget, or extension-owned keys exist that the new release does not
  recognize.
- A mixed schema contains different legacy and canonical values for the same logical field.
- The settings record is missing entirely, as on a genuine first installation.
- The stored settings record is malformed or unreadable; the update must not silently
  replace it and present the replacement as a successful preservation.
- The update process runs multiple times, including after deactivation and reactivation.

## Out of Scope

- Reconstructing configuration that was already erased before this fix is installed.
- Redesigning the settings interface or changing the meaning of existing settings.
- Changing analytics calculations, reports, retention behavior, or tracking behavior beyond
  initializing absent analytics settings.
- Removing or renaming unknown or extension-owned configuration keys. Recognized released
  prefixed keys are canonicalized only through the migration contract.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001 (Umbrella preservation invariant)**: The update MUST preserve the logical value of every existing top-level and
  nested configuration field. A recognized legacy path MAY be replaced by its documented
  canonical path only after the value has been transferred successfully.
- **FR-002 (Required FR-001 coverage: numbers)**: The update MUST preserve WhatsApp numbers, their identifiers, labels, phone
  values, default selection, descriptions, and content assignments.
- **FR-003 (Required FR-001 coverage: presentation)**: The update MUST preserve plugin enablement, button text and appearance,
  display placements, exclusions, message templates, floating-button enablement and
  position, and advanced or catalog-mode settings. For this plugin, reported
  "template/layout/widget" behavior is represented by message templates, placement groups,
  button appearance, and floating-button settings; the supported released schema has no
  separate core layout-selector field.
- **FR-004**: The update MUST distinguish an absent field from an existing field whose value
  is `false`, zero, blank, `null`, or an empty collection.
- **FR-005**: Defaults MUST be added only for fields that do not already exist in the saved
  configuration.
- **FR-006**: When the analytics settings group is wholly absent, the update MUST initialize
  it with the release's documented defaults without changing sibling settings.
- **FR-007**: When the analytics settings group is partially populated, the update MUST
  preserve its existing fields and initialize only its absent fields.
- **FR-008**: The update MUST retain unrecognized legacy or extension-owned settings rather
  than discarding them.
- **FR-009**: Re-running the update MUST produce no further configuration changes after the
  first successful initialization of missing fields.
- **FR-010**: A merchant MUST NOT need to open or save the settings screen to preserve or
  restore configuration after an update.
- **FR-011**: Storefront button visibility, placement, appearance, destination number, and
  prepared message MUST remain consistent with the pre-update configuration immediately
  after the update.
- **FR-012**: A genuinely new installation with no saved configuration MUST still receive
  the complete current default configuration.
- **FR-013**: If the saved configuration cannot be safely interpreted, the update MUST avoid
  silently replacing it with defaults and MUST leave enough diagnostic evidence for support
  to identify the unsuccessful migration.
- **FR-014**: A successful migration MUST persist one canonical copy of each recognized
  logical setting. Recognized mapped legacy leaf keys MUST be removed in the same atomic
  option write. If unknown descendants exist beneath a recognized legacy container, the
  migration MUST retain the minimum structural container and list-index shells required to
  keep every unknown path and value exactly unchanged; empty legacy shells MUST be removed.
- **FR-015**: In a mixed schema, an existing canonical field MUST take precedence, including
  when its value is `false`, zero, blank, `null`, or an empty collection. A mapped legacy
  field supplies a value only when the canonical field is absent.

### Supported Upgrade Matrix

| Row | Source evidence | Source settings schema | Required verification |
|-----|-----------------|------------------------|-----------------------|
| `R1` | Plugin `1.0.0`, `main` at `9a1959e` | Released prefixed schema; analytics absent | Full legacy mapping, pruned-shell preservation, defaults, storefront comparison |
| `R2` | Plugin `1.0.1`, schema change at `4ecc330` | Canonical or mixed, unversioned; analytics absent | Canonical precedence, legacy gap fill, pruned-shell preservation, defaults |
| `R3` | Plugin `1.0.2`, analytics change at `62d4ed2` through `3314ec6` | Canonical or mixed, unversioned; analytics absent, partial, or customized | Analytics preservation/default completion, mixed precedence, idempotence |

Missing-option, malformed-option, and failed-write fixtures are state-safety rows rather than
source-version rows and remain mandatory in addition to `R1`–`R3`. If release records identify
another directly upgradable build before release, the matrix and fixtures MUST be extended.

### Constitution-Aligned Requirements *(mandatory)*

- **Compatibility**: The existing configuration record, all unknown and extension-owned saved
  keys, public button behavior, shortcodes, routes, and storefront selectors MUST remain
  compatible. Recognized released `ctc_chat_*` paths are internal schema aliases and are
  intentionally atomically migrated to documented canonical paths with compatibility handling,
  release notes, and the appropriate patch version bump; their logical values remain unchanged.
- **WordPress/WooCommerce Architecture**: The update MUST follow the plugin's established
  lifecycle and configuration conventions without adding a new external dependency or
  changing the plugin's public structure.
- **Security and Privacy**: The feature MUST NOT expose or transmit saved phone numbers,
  assignments, messages, exclusions, or analytics preferences. The successfully persisted
  configuration MUST contain only one copy of each recognized logical setting; transient
  in-memory mapping MUST NOT be logged or exported. Existing access restrictions remain in
  force.
- **Performance**: For a store with up to 100 WhatsApp numbers and 500 exclusion entries,
  the update MUST finish within 5 seconds and require no merchant interaction.
- **Verification and Evidence**: Release evidence MUST compare complete configurations and
  representative storefront behavior across supported upgrade paths, partial settings,
  explicit false or empty values, repeated runs, and a fresh installation.

### Key Entities *(include if feature involves data)*

- **Saved Plugin Configuration**: The authoritative collection of merchant choices,
  including plugin status, WhatsApp numbers and assignments, button appearance, placements,
  messages, exclusions, widget behavior, advanced options, analytics preferences, and
  unrecognized compatible keys.
- **Default Configuration**: Values supplied for a new installation or for individual fields
  that are genuinely absent after an update.
- **Update Attempt**: A single plugin-update, retry, activation, or maintenance event that
  may initialize missing fields but must not overwrite existing configuration.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001 (Verification of FR-001–FR-003, FR-008, FR-014, and FR-015)**: Across rows `R1`–`R3`, 100% of pre-existing logical
  values remain equivalent after update, unknown key paths and values remain exactly
  unchanged, and only the documented canonical paths, absent defaults, and schema metadata
  may differ.
- **SC-002**: In every legacy fixture without analytics settings, 100% of required analytics
  defaults are available after update while zero unrelated values change.
- **SC-003**: In every partial-settings fixture, only absent fields are added; existing false,
  zero, blank, `null`, empty, and customized values have a 0% overwrite rate. Canonical-only
  fixtures include `null` values as authoritative existing fields.
- **SC-004**: Running the update three consecutive times produces identical configuration
  after the first successful run.
- **SC-005**: Representative product, shop, cart, checkout, thank-you, shortcode, and floating
  button journeys show zero unintended changes in visibility, appearance, destination, or
  prepared message after update.
- **SC-006**: A configured store requires zero manual reconfiguration steps after updating.
- **SC-007**: The update completes within 5 seconds for the defined high-volume configuration
  and the settings screen remains available without recovery action.

## Assumptions

- The supported upgrade matrix is the explicit `R1`–`R3` schema-family matrix above, derived
  from repository version and schema-changing commits. Release review must add any omitted
  directly upgradable build discovered in authoritative distribution records.
- The saved configuration immediately before the update is the authoritative source of
  merchant intent, even when a value differs from current defaults. In a mixed record, the
  canonical field represents the most recent active schema and therefore takes precedence
  over its mapped legacy counterpart.
- Empty values may be intentional and therefore count as existing values.
- Analytics settings use their documented release defaults only when the corresponding
  fields are absent.
- Recovery of configuration erased by an earlier faulty update requires a backup or a
  separate recovery effort and is not part of this prevention fix.
- Fresh installations remain eligible for the full default configuration.
- The initial target for this first canonical settings migration is schema version `1.0.0`.
