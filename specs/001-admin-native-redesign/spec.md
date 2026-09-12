# Feature Specification: WordPress-Native Admin Redesign

**Feature Branch**: `codex/click-to-chat-admin-native-redesign`

**Created**: 2026-06-12

**Status**: In Progress

**Input**: User description: "Transform Aicoso Click to Chat admin UI to match Affiliate Program for WooCommerce native admin pattern — Aicoso logo/header, cross-page tabs, WordPress-native notices and buttons, dashicons only, scoped CSS design tokens, no purple gradient banners or heavy custom styling."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Native Admin Shell (Priority: P1)

As a store manager, I can open any Click to Chat admin page and see a consistent Aicoso product header, breadcrumb, and cross-page tab navigation without legacy purple banners or duplicate page chrome.

**Why this priority**: The shared shell is the foundation for every admin screen migration.

**Independent Test**: Open Settings, Numbers, Templates, and Shortcode routes and confirm `.ctc-wrap`, `.ctc-product-header`, and `.ctc-admin-tabs` render on each page.

**Acceptance Scenarios**:

1. **Given** an admin opens any Click to Chat page, **When** the screen renders, **Then** the Aicoso logo, product title, breadcrumb, and active cross-page tab are visible.
2. **Given** legacy view templates previously contained standalone headers, **When** pages load, **Then** no purple gradient banners or duplicate tab bars appear inside views.
3. **Given** setup warnings exist, **When** the shell renders, **Then** notices use WordPress-native `.notice` markup.

---

### User Story 2 - Admin CSS Design System (Priority: P1)

As a maintainer, I can style Click to Chat admin screens with scoped `--ctc-admin-*` tokens that mirror Affiliate Program conventions.

**Why this priority**: Prevents regression to one-off colors and unscoped global admin overrides.

**Independent Test**: Inspect `admin/css/admin.css` for token usage and confirm legacy gradient/pill button classes are absent from rendered pages.

**Acceptance Scenarios**:

1. **Given** admin CSS loads, **When** styles are scoped under `.ctc-wrap`, **Then** core WordPress admin layout is not globally overridden.
2. **Given** Select2 is used on admin pages, **When** dropdowns render, **Then** styling is scoped under `.ctc-wrap` only.
3. **Given** save/action buttons render, **When** inspected, **Then** they use `button`, `button-primary`, or `button-secondary`.

---

### User Story 3 - Settings Native Tabs And Forms (Priority: P2)

As a store manager, I can navigate Settings sub-sections (General, Display, Exclusions) using WordPress-native `subsubsub` tabs and readable `form-table` layouts.

**Why this priority**: Settings is the most visited admin page and currently uses custom JS tab buttons.

**Independent Test**: Open Settings and switch sub-tabs via URL; confirm Exclusions tab is reachable and settings persist on save.

**Acceptance Scenarios**:

1. **Given** Settings is open, **When** sub-tabs render, **Then** navigation uses `<ul class="subsubsub">` instead of custom buttons.
2. **Given** a settings field is changed, **When** saved, **Then** values persist and a native success notice appears.
3. **Given** a mobile viewport (782px), **When** Settings renders, **Then** no page-level horizontal scroll appears.

---

### User Story 4 - Edge Admin Surfaces (Priority: P2)

As a store manager, I can configure per-product Click to Chat overrides in a native-styled WooCommerce product meta box.

**Why this priority**: Product-level controls are part of the admin experience and currently use legacy styling.

**Independent Test**: Open a product edit screen and confirm meta box fields save and load correctly.

**Acceptance Scenarios**:

1. **Given** a product edit screen, **When** the Click to Chat meta box renders, **Then** it uses native meta box styling without custom gradient chrome.
2. **Given** product overrides are saved, **When** the storefront button renders, **Then** hide/number/message overrides apply correctly.

---

### User Story 5 - Final QA And Release Evidence (Priority: P2)

As a maintainer, I can ship the admin redesign with browser QA, PHP syntax checks, and evidence files documenting validation results.

**Why this priority**: Required for release confidence and parity with Affiliate Program transformation workflow.

**Independent Test**: Run quickstart checks and confirm evidence exists under `docs/evidence/admin-native-redesign/`.

**Acceptance Scenarios**:

1. **Given** all admin routes, **When** browser QA runs, **Then** no console errors and no horizontal scroll on desktop or mobile.
2. **Given** changed PHP files, **When** syntax checks run, **Then** all pass.
3. **Given** transformation is complete, **When** evidence is reviewed, **Then** route checklist and implementation notes exist.

## Functional Requirements

- **FR-001**: All four admin pages MUST render inside a shared native admin shell with Aicoso branding.
- **FR-002**: Cross-page tabs MUST link to Settings, Numbers, Templates, and Shortcode routes with correct active state.
- **FR-003**: Admin CSS MUST use scoped design tokens and MUST NOT apply global WordPress admin overrides outside `.ctc-wrap`.
- **FR-004**: Settings sub-tabs MUST use WordPress `subsubsub` navigation.
- **FR-005**: Simple settings fields SHOULD use `form-table` markup.
- **FR-006**: Product meta box MUST retain existing save/load behavior with native styling.
- **FR-007**: Existing settings option keys, shortcodes, and storefront button behavior MUST be preserved.
- **FR-008**: Release evidence MUST be stored under `docs/evidence/admin-native-redesign/`.

## Success Criteria

- All four main admin pages pass desktop browser QA with Aicoso header and native notices.
- Legacy purple gradient admin banners are fully removed.
- Settings Exclusions tab is reachable and functional via native sub-tab navigation.
- Product meta box passes save/load QA on at least one simple and one variable product.
- PHP syntax checks pass for all changed admin files.
- Evidence folder contains route checklist and status notes.

## Out of Scope

- Frontend/public button styling refactor (separate from admin transformation).
- New checkout, cart, or direct-sales flows on aicoso.com.
- Changes to WordPress core or WooCommerce core files.

## Assumptions

- Reference design: Affiliate Program for WooCommerce admin native pattern in the same pre-prod Studio site.
- Local validation URL: `http://localhost:8882` (pre-prod Studio site).
- Dashicons and inherited WordPress admin fonts only; no external icon libraries in admin.
