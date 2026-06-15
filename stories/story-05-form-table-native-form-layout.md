# Story 05: form-table Native Form Layout

## Description

Migrate simple settings fields from custom `.ctc-chat-form-group` grid layouts to WordPress-native `form-table` markup for consistency with Affiliate Program and core WordPress admin screens.

## Acceptance Criteria

- General and Display settings sections use `<table class="form-table">` for label/control pairs.
- Toggle switches and Select2 fields remain functional inside `form-table` rows.
- Complex multi-column layouts (numbers table, template editor) may retain custom grids where `form-table` is inappropriate.
- Field labels use `<th scope="row">` and controls use `<td>`.
- Help text uses `.description` class under controls.
- Save flow persists all settings correctly after layout migration.
- Mobile layout at 782px remains usable without horizontal scroll.

## Technical Details

**Files to modify**

- `admin/views/settings-page.php`
- `admin/css/admin.css` (reduce `.ctc-chat-form-group` usage; add minimal `form-table` spacing overrides under `.ctc-wrap` if needed)

**Keep custom layout for**

- WhatsApp Numbers list/table (Story 05 scope: settings form only)
- Message Templates editor
- Shortcode reference blocks

## Dev Notes

- Reference: Affiliate Program settings `form-table` pattern in native settings screen.
- Depends on: Story 04 (sub-tabs should be stable before form refactor).
- Blocks: Story 07.
- **Status (2026-06-12):** Implemented. General, Button, Display, and Exclusions tabs use `form-table` with `h2.title` section headings.
