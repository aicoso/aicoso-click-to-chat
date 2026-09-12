# Story 03: View Template Migration And Native Notices

## Description

Migrate all admin view templates to the shared shell pattern by removing legacy chrome, standardizing save/action buttons, and converting custom warning markup to WordPress-native notices.

## Acceptance Criteria

- Settings, Numbers, Templates, and Shortcode views contain no standalone page headers or duplicate tab navigation.
- Save and add buttons use `button button-primary` and `button-secondary`.
- Advanced-settings warning uses `.notice.notice-warning.inline` instead of custom warning boxes.
- Shortcode page has no purple banner, emoji decoration, or malformed closing tags.
- All views render correctly inside `render_admin_shell_open()` / `render_admin_shell_close()`.
- Form submission and AJAX flows continue to work unchanged.

## Technical Details

**Files to modify**

- `admin/views/settings-page.php`
- `admin/views/numbers-page.php`
- `admin/views/templates-page.php`
- `admin/views/shortcode-page.php`

**Per-view changes**

| View | Remove | Add / Keep |
|------|--------|------------|
| Settings | Custom header, inline tabs chrome | Form content only; shell from admin class |
| Numbers | Custom header | Table and modal content |
| Templates | Custom header | Template list and editor |
| Shortcode | Purple banner, emoji header | Native heading + shortcode reference content |

## Dev Notes

- Reference: Affiliate Program settings and shortcodes template cleanup (Story 26).
- Depends on: Stories 01, 02.
- Blocks: Stories 05, 07.
- **Status (2026-06-12):** Implemented and visually verified on all four admin pages.
