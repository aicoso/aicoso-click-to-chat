---

description: "Dependency-ordered tasks for preserving Click to Chat settings during updates"
---

# Tasks: Preserve Settings During Plugin Updates

**Input**: Design documents from `/specs/003-fix-settings-reset/`

**Prerequisites**: [plan.md](plan.md), [spec.md](spec.md), [research.md](research.md),
[data-model.md](data-model.md), [settings migration contract](contracts/settings-migration-contract.md)

**Verification**: The migration is persistence-critical. Every story includes WordPress-backed
integration fixtures and evidence. Automated fixtures MUST be observed failing before their
corresponding implementation tasks begin.

**Organization**: Tasks are grouped by user story. The migrator is a shared file, so story
implementation proceeds in priority order; independent fixture and evidence work is marked
parallel only when it touches different files.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel because it uses a different file and has no unmet dependency.
- **[Story]**: Maps the task to US1, US2, or US3.
- Every task names the exact repository-relative file it changes or validates.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Establish the migration fixture runner and evidence structure without changing
runtime behavior.

- [X] T001 Create the WP-CLI fixture runner bootstrap, option snapshot/restore helpers, assertion reporting, and guaranteed cleanup in `tests/integration/settings-migration-fixtures.php`
- [X] T002 [P] Create the evidence index with environment, source-version, target-version, fixture, browser, risk, and release-recommendation sections in `docs/evidence/settings-update-preservation/README.md`

**Checkpoint**: The disposable-site test harness can run an empty suite without leaving option changes.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Add the shared migration boundary and lifecycle ordering required by all stories.

**CRITICAL**: No user-story implementation begins until this phase is complete.

- [X] T003 Create `CTC_Chat_Settings_Migrator` with target schema constant `1.0.0`, option-name constants, source-schema detection, strict key-existence helper, and a public migration entry point in `includes/class-ctc-chat-settings-migrator.php`
- [X] T004 Load the migrator before installer initialization and remove the stale pre-migration settings snapshot/write path from activation in `aicoso-click-to-chat.php`
- [X] T005 Invoke settings migration before the database-version early return on normal updates and through the same entry point on activation in `includes/class-ctc-chat-install.php`
- [X] T006 Add shared fixture assertions for logical mapped-value equality, exact unknown-path equality, permitted path/default differences, schema marker state, diagnostic privacy, and option-write counts in `tests/integration/settings-migration-fixtures.php`

**Checkpoint**: Normal update and activation reach one migrator entry point before runtime
classes cache settings, while the fixture suite still fails because story behavior is not
implemented.

---

## Phase 3: User Story 1 - Upgrade Without Configuration Loss (Priority: P1) MVP

**Goal**: Translate the released prefixed configuration into the canonical schema without
losing existing or unknown values and without changing storefront behavior.

**Independent Test**: Seed a complete released-schema configuration with non-default,
false, blank, empty, Unicode, multiline, multi-number, assignment, exclusion, and advanced
values; run a normal update; verify every logical value and representative storefront button
remain unchanged.

### Verification for User Story 1

- [X] T007 [US1] Add failing full legacy-schema, explicit false/blank/empty, multiline message-template, placement/button appearance, floating-button position, multi-number assignment, exclusion, and advanced-setting fixtures in `tests/integration/settings-migration-fixtures.php`
- [ ] T008 [P] [US1] Capture the option and storefront baseline for each supported upgrade-matrix row (R1-R3) covering settings, numbers, templates, product, shop, cart, checkout, thank-you, shortcode, and floating journeys in `docs/evidence/settings-update-preservation/browser-qa.md`

### Implementation for User Story 1

- [X] T009 [US1] Implement legacy-only top-level group mapping and recognized-field cleanup in `includes/class-ctc-chat-settings-migrator.php`; retain a legacy container shell when it contains unknown children, and leave mixed-schema conflict precedence to T023
- [X] T010 [US1] Implement nested button, placement, message-template, exclusion, and advanced-setting mapping without truthiness checks or re-sanitization in `includes/class-ctc-chat-settings-migrator.php`
- [X] T011 [US1] Implement ordered WhatsApp-number record and nested assignment mapping while preserving IDs, incomplete records, unknown fields, and list order in `includes/class-ctc-chat-settings-migrator.php`
- [ ] T012 [US1] Run the US1 fixtures and record before/after logical-value comparisons and pass/fail output in `docs/evidence/settings-update-preservation/fixture-results.md`
- [ ] T013 [US1] Repeat the captured admin and storefront journeys after migration and record zero unintended changes in `docs/evidence/settings-update-preservation/browser-qa.md`

**Checkpoint**: A released prefixed configuration is usable by current readers immediately
after update, every logical value and unknown field is preserved, and each recognized setting
is stored once under its canonical path.

---

## Phase 4: User Story 2 - Initialize Only Newly Introduced Settings (Priority: P2)

**Goal**: Add current defaults only for genuinely absent fields, including fresh-install and
partial analytics cases, without overwriting any existing value.

**Independent Test**: Run fixtures for missing analytics, partial analytics, fully customized
analytics, canonical partial settings, and a missing option; verify only absent fields are
added and a fresh installation receives complete defaults.

### Verification for User Story 2

- [X] T014 [US2] Add failing missing, partial, customized, false-valued, null-valued, and empty-valued analytics plus canonical-only null, canonical-partial, and fresh-install fixtures in `tests/integration/settings-migration-fixtures.php`
- [X] T015 [P] [US2] Document expected analytics defaults and high-volume fixture inputs for 100 numbers and 500 exclusions in `docs/evidence/settings-update-preservation/fixture-results.md`

### Implementation for User Story 2

- [X] T016 [US2] Implement recursive add-only default completion using strict key existence (including authoritative `null`) while retaining unknown top-level and nested fields in `includes/class-ctc-chat-settings-migrator.php`
- [X] T017 [US2] Implement missing-option fresh installation, successful settings persistence, schema-version advancement, and prior-error cleanup in `includes/class-ctc-chat-settings-migrator.php`
- [X] T018 [US2] Ensure activation delegates fresh and existing configuration normalization exclusively through installer/migrator orchestration in `aicoso-click-to-chat.php`
- [ ] T019 [US2] Run US2 and high-volume fixtures, verify the five-second budget and zero unrelated overwrites, and append results in `docs/evidence/settings-update-preservation/fixture-results.md`
- [ ] T020 [US2] Verify existing and newly initialized analytics values on the settings screen and append browser results in `docs/evidence/settings-update-preservation/browser-qa.md`

**Checkpoint**: Existing stores receive only missing analytics/default fields, and new stores
receive complete defaults without a separate settings-save action.

---

## Phase 5: User Story 3 - Safe and Repeatable Updates (Priority: P3)

**Goal**: Make migration retry-safe, no-write after convergence, and non-destructive for
malformed or interrupted states.

**Independent Test**: Run migration three times, repeat deactivate/activate cycles, seed mixed
and malformed records, and simulate an unsuccessful persistence attempt; verify stable values,
canonical conflict precedence, safe diagnostics, and retry behavior.

### Verification for User Story 3

- [ ] T021 [US3] Add failing mixed-schema conflict (including false/zero/blank/null/empty canonical winners), recognized legacy cleanup, unknown-key equality including nested fields under pruned container shells, three-run idempotence, no-write fast-path, malformed-option, failed-write, and retry fixtures in `tests/integration/settings-migration-fixtures.php`
- [X] T022 [P] [US3] Add the diagnostic privacy and retry evidence checklist without merchant settings payloads in `docs/evidence/settings-update-preservation/fixture-results.md`

### Implementation for User Story 3

- [X] T023 [US3] Implement mixed-schema canonical precedence, atomic recognized-field cleanup with unknown container-shell retention, unchanged-value no-write behavior, and independent `1.0.0` settings-schema version checks in `includes/class-ctc-chat-settings-migrator.php`
- [X] T024 [US3] Implement malformed-input abort, failed-write version holdback, non-sensitive error marker creation, successful-retry cleanup, and stable result codes in `includes/class-ctc-chat-settings-migrator.php`
- [ ] T025 [US3] Run the full fixture suite three consecutive times plus repeated deactivate/activate cycles and record settings hashes, write counts, diagnostics, and retry results in `docs/evidence/settings-update-preservation/fixture-results.md`

**Checkpoint**: Converged settings are not rewritten, unsafe input is never replaced, and
failed attempts remain recoverable.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Complete compatibility validation, release metadata, documentation, and evidence.

- [X] T026 [P] Run `php -l` on `aicoso-click-to-chat.php`, `includes/class-ctc-chat-install.php`, `includes/class-ctc-chat-settings-migrator.php`, and `tests/integration/settings-migration-fixtures.php`, then record results in `docs/evidence/settings-update-preservation/fixture-results.md`
- [ ] T027 Run repository PHPCS for the changed PHP files and resolve all migration-related findings in `aicoso-click-to-chat.php`, `includes/class-ctc-chat-install.php`, `includes/class-ctc-chat-settings-migrator.php`, and `tests/integration/settings-migration-fixtures.php`
- [X] T028 [P] Update the patch version, stable tag, changelog, and upgrade notice for the preservation fix in `aicoso-click-to-chat.php`, `package.json`, and `readme.txt`
- [ ] T029 Execute every scenario in `specs/003-fix-settings-reset/quickstart.md` and finalize tested versions, environment, results, unresolved risks, and release recommendation in `docs/evidence/settings-update-preservation/README.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Starts immediately.
- **Foundational (Phase 2)**: Depends on Setup and blocks every user story.
- **US1 (Phase 3)**: Depends on Foundational and establishes legacy-to-canonical mapping.
- **US2 (Phase 4)**: Depends on US1 because default completion operates on the canonical
  result produced by legacy mapping.
- **US3 (Phase 5)**: Depends on US1 and US2 so idempotence and retry checks exercise the full
  normalized result.
- **Polish (Phase 6)**: Depends on all selected user stories.

### User Story Dependency Graph

```text
Setup → Foundation → US1 (preserve/migrate) → US2 (add missing defaults)
                                           → US3 (repeat/failure safety)
US1 + US2 + US3 → Release validation
```

### Within Each User Story

- Add and observe failing fixtures before implementation.
- Implement mapping or lifecycle behavior before running acceptance evidence.
- Do not parallelize tasks that edit `includes/class-ctc-chat-settings-migrator.php`.
- Complete the story checkpoint before starting the next story.

### Parallel Opportunities

- T001 and T002 can run in parallel.
- T007 and T008 can run in parallel after Foundation.
- T014 and T015 can run in parallel after US1.
- T021 and T022 can run in parallel after US2.
- T026 and T028 can run in parallel after all story checkpoints.

---

## Parallel Examples

### User Story 1

```text
Task T007: Add legacy migration fixtures in tests/integration/settings-migration-fixtures.php
Task T008: Capture pre-update browser baseline in docs/evidence/settings-update-preservation/browser-qa.md
```

### User Story 2

```text
Task T014: Add analytics/default fixtures in tests/integration/settings-migration-fixtures.php
Task T015: Document expected defaults and scale fixture in docs/evidence/settings-update-preservation/fixture-results.md
```

### User Story 3

```text
Task T021: Add idempotence/failure fixtures in tests/integration/settings-migration-fixtures.php
Task T022: Add diagnostic privacy checklist in docs/evidence/settings-update-preservation/fixture-results.md
```

---

## Implementation Strategy

### MVP First

1. Complete Setup and Foundational tasks T001–T006.
2. Complete US1 tasks T007–T013.
3. Stop and validate the real released-version update and storefront continuity.
4. Treat this as the minimum emergency recovery increment; do not release until US2 and US3
   safeguards are also complete because the production fix must initialize analytics and be
   retry-safe.

### Incremental Delivery

1. **US1** restores existing released configurations.
2. **US2** safely introduces missing analytics and current defaults.
3. **US3** makes the combined migration stable under retries and malformed states.
4. **Polish** supplies syntax, standards, release metadata, and evidence gates.

## Notes

- `[P]` means different files and no unmet dependency; it does not authorize simultaneous
  edits to the migrator or shared fixture runner.
- Replace recognized legacy fields only after successful canonical mapping; preserve unknown
  and extension-owned keys exactly, including shells containing unknown children, in this
  emergency patch.
- Never use `empty()` to decide whether a saved field exists.
- Do not include phone numbers, messages, assignments, URLs, or complete settings payloads in
  diagnostic or evidence files.
- Commit after each story checkpoint or other logical group if using the Git hook.
