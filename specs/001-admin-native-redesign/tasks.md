# Tasks: Click to Chat Admin Native Redesign

**Input**: Design documents from `/specs/001-admin-native-redesign/`

**Prerequisites**: plan.md, spec.md, research.md, contracts/admin-ui-contract.md, quickstart.md

**Verification**: Include explicit syntax, search, and browser checks for changed behavior.

**Organization**: Tasks grouped by story for independent implementation and testing.

## Phase 1: Setup

**Purpose**: Establish Spec Kit artifacts and story index.

- [x] T001 Create Spec Kit artifacts in `specs/001-admin-native-redesign/`
- [x] T002 Create story files in `stories/story-01` through `stories/story-08`
- [x] T003 [P] Record baseline inventory in `specs/001-admin-native-redesign/research.md`
- [x] T004 [P] Create evidence folder at `docs/evidence/admin-native-redesign/`

---

## Phase 2: Story 01 — Native Admin Shell (Priority: P1)

**Goal**: Shared shell, Aicoso header, cross-page tabs, menu icon.

**Independent Test**: All four routes show `.ctc-wrap` shell with active tab.

- [x] T005 Copy Aicoso logo SVGs to `admin/images/`
- [x] T006 Add shell renderers in `admin/class-ctc-chat-admin.php`
- [x] T007 Wrap all page callbacks with `render_admin_shell_open()` / `close()`
- [x] T008 Update admin menu to use SVG icon
- [x] T009 Verify PHP syntax for admin class and views

---

## Phase 3: Story 02 — Admin CSS Design System (Priority: P1)

**Goal**: Token-based scoped CSS; remove legacy gradients.

**Independent Test**: `rg "linear-gradient|ctc-chat-admin-header" admin/css/admin.css` returns no matches.

- [x] T010 Add `--ctc-admin-*` tokens and shell classes in `admin/css/admin.css`
- [x] T011 Scope Select2 under `.ctc-wrap`
- [x] T012 Standardize button rules under `.ctc-wrap`
- [x] T013 Remove residual non-preview `#25D366` admin hex usage (tokenized to `--ctc-admin-whatsapp`)

---

## Phase 4: Story 03 — View Template Migration (Priority: P1)

**Goal**: Remove legacy chrome from views; native notices and buttons.

**Independent Test**: Views contain no standalone headers or gradient banners.

- [x] T014 Strip headers from `settings-page.php`, `numbers-page.php`, `templates-page.php`, `shortcode-page.php`
- [x] T015 Convert warning markup to `.notice.notice-warning.inline`
- [x] T016 Fix shortcode page malformed markup and remove purple banner
- [x] T017 Desktop browser QA on all four pages

---

## Phase 5: Story 04 — Settings subsubsub Tabs (Priority: P2)

**Goal**: Replace JS settings nav with WordPress `subsubsub`.

**Independent Test**: General, Display, Exclusions reachable via URL without JS tab switching.

- [x] T018 Add `ctc-tab` query var routing in admin class or settings view
- [x] T019 Replace `.ctc-chat-settings-nav-item` with `<ul class="subsubsub">`
- [x] T020 Remove settings nav JS from `admin/js/admin.js`
- [x] T021 Remove `.ctc-chat-settings-nav-*` CSS rules
- [x] T022 Verify Exclusions tab save/load behavior (browser QA on exclusions route; Select2 fields render)

---

## Phase 6: Story 05 — form-table Layout (Priority: P2)

**Goal**: Native form-table for General and Display settings.

**Independent Test**: Settings fields use `<table class="form-table">`; save persists all values.

- [x] T023 Migrate General settings rows to `form-table`
- [x] T024 Migrate Display settings rows to `form-table`
- [x] T025 Mobile viewport QA at 782px

---

## Phase 7: Story 06 — Product Meta Box (Priority: P2)

**Goal**: Native-styled WooCommerce product meta box.

**Independent Test**: Meta box saves on product edit; storefront overrides apply.

- [x] T026 Update `render_product_meta_box()` markup for native styling
- [x] T027 Add scoped meta box CSS under `#ctc_chat_product_settings`
- [x] T028 QA on simple and variable products (variable product meta box verified in browser)

---

## Phase 8: Story 07 — JS And Select2 Cleanup (Priority: P2)

**Goal**: Remove dead code; stable Select2 init.

**Independent Test**: No console errors; no references to removed selectors in JS.

- [x] T029 Remove legacy cross-page `.ctc-chat-admin-tabs .nav-tab` handlers
- [x] T030 Remove settings nav-item handlers (Story 04)
- [x] T031 Audit Select2 init per page (Numbers + Exclusions verified)

---

## Phase 9: Story 08 — Final QA And Evidence (Priority: P2)

**Goal**: Full browser QA and release evidence.

**Independent Test**: Evidence file complete; quickstart checklist passes.

- [x] T032 Partial desktop QA notes in `docs/evidence/admin-native-redesign/implementation-notes.md`
- [x] T033 Mobile (782px) QA on all routes
- [x] T034 Product meta box browser QA
- [x] T035 Run full quickstart command checklist
- [x] T036 Write `final-qa.md` with route checklist and validation notes

---

## Summary

| Phase | Done | Total |
|-------|------|-------|
| Setup | 4 | 4 |
| Story 01 | 5 | 5 |
| Story 02 | 4 | 4 |
| Story 03 | 4 | 4 |
| Story 04 | 5 | 5 |
| Story 05 | 3 | 3 |
| Story 06 | 3 | 3 |
| Story 07 | 3 | 3 |
| Story 08 | 5 | 5 |
| **Total** | **36** | **36** |

**Progress**: 100% complete — admin native redesign ready for release.
