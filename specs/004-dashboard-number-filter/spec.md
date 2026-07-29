# Feature Specification: Dashboard WhatsApp Number Filter

**Feature Branch**: `004-dashboard-number-filter`

**Created**: 2026-07-28

**Status**: Draft

**Input**: User description: "Allow administrators to filter Analytics Dashboard metrics and widgets by an individual configured WhatsApp number."

## Clarifications

### Session 2026-07-28

- Q: What should happen when the selected WhatsApp number is invalid or has been removed? → A: Switch to All numbers and show a notice that the previous selection is unavailable.
- Q: When should the WhatsApp number filter be visible? → A: Always show it, including when one or no numbers are configured, with All numbers and Unattributed always available.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Analyze One WhatsApp Number (Priority: P1)

As a store administrator managing multiple WhatsApp numbers, I want to select one number on
the Analytics Dashboard so that I can understand the clicks and purchase intent associated
with that number without data from other numbers being mixed into the results.

**Why this priority**: This is the core user value. Without consistent per-number results,
administrators cannot compare teams, departments, or number assignments accurately.

**Independent Test**: Configure two numbers, record distinct clicks for each, select one
number, and verify that every dashboard metric and widget displays only that number's data.

**Acceptance Scenarios**:

1. **Given** two configured numbers with tracked clicks, **When** an administrator selects
   one number and applies the filters, **Then** every dashboard metric and widget reflects
   only clicks attributed to the selected number.
2. **Given** a selected number with no clicks in the chosen date range, **When** the filters
   are applied, **Then** the dashboard displays valid zero or unavailable states rather than
   data belonging to another number.
3. **Given** a selected number, **When** the administrator changes the date preset or custom
   date range, **Then** the number selection remains active and the refreshed results use
   both the number and date filters.

---

### User Story 2 - Review All and Unattributed Activity (Priority: P2)

As a store administrator, I want to switch between all numbers and unattributed clicks so
that I can reconcile the overall dashboard with older or incomplete tracking records.

**Why this priority**: Existing stores can contain clicks without number attribution. Making
those records visible prevents filtered totals from appearing incorrect or unexplained.

**Independent Test**: Seed clicks for two numbers plus clicks with no number attribution,
then verify that All numbers includes every click and Unattributed includes only records
without a number identity.

**Acceptance Scenarios**:

1. **Given** attributed and unattributed clicks, **When** All numbers is selected, **Then**
   dashboard results match the existing unfiltered totals.
2. **Given** clicks without number attribution, **When** Unattributed is selected, **Then**
   every dashboard metric and widget uses only those clicks.
3. **Given** historical clicks for a number that is no longer configured, **When** All
   numbers is selected, **Then** those clicks remain in the overall totals and are not
   reassigned to a current number or to Unattributed.

---

### User Story 3 - Compare Like-for-Like Periods (Priority: P3)

As a store administrator, I want prior-period comparisons to use the same WhatsApp number
filter so that changes shown by the dashboard are meaningful.

**Why this priority**: A current period filtered to one number cannot be accurately compared
with an unfiltered prior period.

**Independent Test**: Record different current and prior-period activity for multiple
numbers, select one number with comparison enabled, and verify that both values use only the
selected number.

**Acceptance Scenarios**:

1. **Given** prior-period comparison is enabled, **When** a number is selected, **Then** each
   current and prior value is calculated using that same number.
2. **Given** comparison is disabled, **When** a number is selected, **Then** the dashboard
   shows filtered current-period results without comparison values.
3. **Given** the default-number designation or display name changes, **When** historical
   analytics are viewed, **Then** stored clicks remain associated with their original number
   identity.

### Edge Cases

- No WhatsApp numbers are currently configured.
- Only one WhatsApp number is configured.
- A configured number has no activity in the selected date range.
- All tracked clicks in the selected range are unattributed.
- A number was renamed, made default, removed, or replaced after clicks were recorded.
- The selected number is removed in another session before the dashboard refreshes.
- A malformed or unauthorized filter value is submitted.
- Rapid filter or date changes return responses in a different order from the requests.
- The toolbar is viewed on a narrow screen or at increased browser zoom.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The Analytics Dashboard MUST always provide a clearly labeled WhatsApp number
  filter, including when one or no WhatsApp numbers are currently configured.
- **FR-002**: The filter MUST include an All numbers choice that preserves the dashboard's
  existing unfiltered behavior.
- **FR-003**: The filter MUST list configured WhatsApp numbers using their administrator
  display names and masked phone numbers.
- **FR-004**: The filter MUST include an Unattributed choice for clicks that have no recorded
  number identity.
- **FR-005**: All numbers MUST be the initial selection when the dashboard is opened without
  an explicit valid number selection.
- **FR-006**: Applying the dashboard filters MUST apply the selected number consistently to
  WhatsApp Clicks, Unique Clicks, High-Intent Clicks, Total Cart Value at Click, Mobile Share,
  Top Placement, Click Trend, WhatsApp Intent Funnel, Top Products, and Top WhatsApp Numbers.
- **FR-007**: A selected number MUST remain active when an administrator changes a date
  preset, custom date range, or comparison setting during the current dashboard session.
- **FR-008**: Prior-period values MUST use the same number selection as current-period values.
- **FR-009**: Selecting a number with no matching activity MUST display valid empty, zero, or
  unavailable states without falling back to another number's data.
- **FR-010**: Unattributed results MUST include records with no usable number identity and
  MUST exclude clicks attributed to a configured or historical number identity.
- **FR-011**: Changing a number's display name or default status MUST NOT reassign historical
  clicks to a different number.
- **FR-012**: Removing a configured number MUST NOT remove or reassign its historical clicks
  from All numbers totals.
- **FR-013**: When a number selection is invalid, stale, tampered with, or removed before
  refresh, the dashboard MUST switch to All numbers, refresh all widgets using that scope,
  and show a notice that the previous selection is unavailable.
- **FR-014**: The number filter MUST be operable with a keyboard and have an accessible label.
- **FR-015**: The filter toolbar MUST remain usable without horizontal page scrolling at the
  supported narrow admin viewport.
- **FR-016**: User-visible filter labels and messages MUST be translatable.
- **FR-017**: Full WhatsApp phone numbers MUST NOT be exposed by the filter or its supporting
  dashboard output.
- **FR-018**: Existing analytics collected before this feature MUST remain available, and
  existing unfiltered dashboard results MUST not change when All numbers is selected.

### Constitution-Aligned Requirements *(mandatory)*

- **Compatibility**: Existing analytics records, database schema, storefront tracking,
  WhatsApp links, button behavior, shortcodes, hooks, admin routes, option keys, and existing
  unfiltered dashboard behavior MUST remain compatible. The feature MUST require no
  reassignment or destructive migration of historical clicks.
- **WordPress/WooCommerce Architecture**: The filter MUST reuse the existing administrator
  capability and protected dashboard request flow, use the project's existing configured
  number identities, load assets only on the analytics screens, and use native
  internationalization behavior. No new runtime dependency is permitted.
- **Security and Privacy**: Filter input MUST be validated at the request boundary, dashboard
  access MUST retain the existing capability and request-integrity protections, and all
  output MUST be safe for its display context. Only display names and masked number values
  may be shown. The feature MUST not add personal data, change retention, or alter deletion
  and export behavior.
- **Performance**: For a store with up to 100 configured numbers and 100,000 retained click
  records, applying a number filter MUST display the complete dashboard within 2 seconds in
  at least 95% of supported test runs and MUST not add storefront work.
- **Verification and Evidence**: Verification MUST cover All numbers, two distinct configured
  numbers, Unattributed, zero-result ranges, prior-period comparison, renamed/default/removed
  numbers, invalid selections, keyboard operation, responsive layout, syntax and coding
  standards, and affected dashboard routes. Reproducible results and known limitations MUST
  be recorded under `docs/evidence/dashboard-number-filter/` before release.

### Key Entities *(include if feature involves data)*

- **WhatsApp Number Selection**: The administrator's active dashboard scope: All numbers, one
  configured stable number identity, or Unattributed.
- **Configured WhatsApp Number**: A managed number with a stable identity, administrator
  display name, masked phone representation, default status, and current assignments.
- **Analytics Click Record**: A historical WhatsApp click that may contain a stable number
  identity or may be unattributed.
- **Dashboard Result Set**: The metrics, trend, funnel, product ranking, number ranking, and
  comparison values produced for one date range and one number selection.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: In a controlled dataset containing two configured numbers and unattributed
  clicks, 100% of dashboard metrics and widgets match the expected records for each filter
  choice.
- **SC-002**: All numbers results remain identical to the dashboard's pre-feature results for
  the same date range and comparison setting.
- **SC-003**: Current and prior-period values use the same selected number in 100% of
  comparison verification scenarios.
- **SC-004**: An administrator can select a number and refresh the complete dashboard in no
  more than two interactions.
- **SC-005**: The complete filtered dashboard appears within 2 seconds in at least 95% of
  supported performance test runs with 100,000 retained click records.
- **SC-006**: All number-filter controls can be reached, understood, and operated using only
  a keyboard at 100% and 200% browser zoom.
- **SC-007**: No full WhatsApp phone number appears in the filter, dashboard markup, or
  visible filtered results during privacy verification.
- **SC-008**: Zero historical records are deleted, reassigned, or modified when the feature
  is enabled or used.

## Assumptions

- Dashboard users already have the required analytics administration capability.
- Existing click tracking records a stable number identity when attribution is available.
- All numbers is the safest backward-compatible default and does not need to persist across
  browser sessions.
- The selected number remains active for the current page session but is not saved as a user
  preference in the first release.
- Filter changes are applied through the dashboard's existing Apply interaction; date preset
  actions refresh immediately while retaining the active number.
- Top WhatsApp Numbers follows the global filter like every other widget, even though an
  individual-number selection normally produces one matching row.
- Historical clicks belonging to a removed number remain included in All numbers; making
  removed numbers individually selectable is outside the first-release scope.