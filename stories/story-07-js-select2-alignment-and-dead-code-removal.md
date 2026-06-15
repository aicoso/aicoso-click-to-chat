# Story 07: JS, Select2 Alignment, And Dead Code Removal

## Description

Align admin JavaScript with the native shell transformation by removing legacy tab handlers, dead code paths, and ensuring Select2 initialization is scoped and consistent across all admin pages.

## Acceptance Criteria

- Legacy `.ctc-chat-admin-tabs .nav-tab` click handlers are removed from `admin.js`.
- Legacy `.ctc-chat-settings-nav-item` handlers are removed after Story 04 ships.
- Select2 fields initialize only on pages that contain them; no duplicate initialization on tab switch.
- AJAX save flows for numbers and templates continue to work.
- No console errors on any of the four admin pages.
- No references to removed CSS class names remain in JS.

## Technical Details

**Files to modify**

- `admin/js/admin.js`
- `admin/class-ctc-chat-admin.php` (enqueue conditions if needed)

**Verify**

- Settings page Select2 (exclusion categories, pages, etc.)
- Numbers page country/number selectors
- Templates page placeholders

## Dev Notes

- Partial cleanup already done: legacy cross-page nav-tab handlers removed.
- Depends on: Stories 03, 04.
- Blocks: Story 08.
- **Status (2026-06-12):** Complete. Settings nav JS removed; Select2 verified on Numbers and Exclusions pages.
