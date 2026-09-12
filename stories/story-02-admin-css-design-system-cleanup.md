# Story 02: Admin CSS Design System Cleanup

## Description

Replace the legacy purple-gradient admin styling with a token-based Aicoso admin design system scoped under `.ctc-wrap`, mirroring Affiliate Program's `--apf-admin-*` pattern as `--ctc-admin-*`.

## Acceptance Criteria

- Admin CSS uses `:root` design tokens for colors, spacing, typography, and borders.
- Legacy purple gradient banners, pill buttons, and custom warning boxes are removed.
- `.ctc-wrap`, `.ctc-product-header`, `.ctc-admin-tabs`, and `.ctc-native-screen` classes are defined and used consistently.
- Select2 styling is scoped under `.ctc-wrap` only.
- Button classes use WordPress-native `button`, `button-primary`, and `button-secondary`.
- Admin menu icon rule `#toplevel_page_click-to-chat .wp-menu-image img` is present.
- Remaining non-preview `#25D366` hex usage in admin CSS is removed or scoped to preview contexts only.
- No page-level horizontal scroll on desktop.

## Technical Details

**Files to modify**

- `admin/css/admin.css`

**Token categories**

- Surface colors (`--ctc-admin-bg`, `--ctc-admin-surface`)
- Text colors (`--ctc-admin-text`, `--ctc-admin-text-muted`)
- Border and radius (`--ctc-admin-border`, `--ctc-admin-radius`)
- Tab and header spacing (`--ctc-admin-header-gap`, `--ctc-admin-tab-padding`)

**Remove**

- `.ctc-chat-admin-header` gradient styles
- `.ctc-chat-admin-tabs .nav-tab` custom styling (replaced by `.ctc-admin-tabs`)
- Legacy `.ctc-chat-warning-box` (replaced by `.notice.notice-warning.inline`)

## Dev Notes

- Reference: Affiliate Program `admin/css/affiliate-program-for-woocommerce-admin.css` token block.
- Depends on: Story 01.
- Blocks: Stories 03–07.
- **Status (2026-06-12):** Core token system and cleanup implemented. Residual green hex in catalog-mode/toggle labels tracked for follow-up in this story.
