# Implementation Notes: Click to Chat Admin Native Redesign

**Date**: 2026-06-12  
**Site**: Pre-prod Studio (`http://localhost:8882`)  
**Spec**: `specs/001-admin-native-redesign/`

## Completed (Stories 01–03)

### Story 01 — Native admin shell

- Added shell helpers to `admin/class-ctc-chat-admin.php`:
  - `get_logo_url()`, `get_menu_icon_url()`
  - `render_product_header()`, `render_admin_tabs()`, `render_setup_status_notices()`
  - `render_admin_shell_open()`, `render_admin_shell_close()`
- Copied Aicoso SVG assets from Affiliate Program to `admin/images/`
- Admin menu uses SVG icon instead of dashicons-whatsapp
- All four page callbacks wrap views in shared shell

### Story 02 — CSS design system

- Rewrote `admin/css/admin.css` with `--ctc-admin-*` tokens
- Added `.ctc-wrap`, `.ctc-product-header`, `.ctc-admin-tabs`, `.ctc-native-screen`
- Scoped Select2 and button standardization under `.ctc-wrap`
- Removed purple gradient banners and legacy warning box styles

**Follow-up**: Some `#25D366` hex remains in admin CSS for catalog-mode labels/toggles (non-preview contexts).

### Story 03 — View template migration

- Removed legacy headers from all four view templates
- Converted advanced warning to `.notice.notice-warning.inline`
- Shortcode page: removed purple banner/emoji; fixed markup
- Save buttons use `button button-primary` / `button-secondary`

### Story 07 (partial)

- Removed legacy `.ctc-chat-admin-tabs .nav-tab` JS handlers from `admin/js/admin.js`

## Browser QA (Partial — Story 08)

**Environment**: Desktop, ~1905px viewport, in-app browser

| Route | Header + tabs | Native notices | No gradients | Console |
|-------|---------------|----------------|--------------|---------|
| Settings | Pass | Pass | Pass | Pass |
| Numbers | Pass | Pass | Pass | Pass |
| Templates | Pass | Pass | Pass | Pass |
| Shortcode | Pass | Pass | Pass | Pass |

**Not yet tested**:

- Mobile 782px viewport
- Settings Exclusions tab (blocked on Story 04)
- Product meta box (Story 06)
- Save-flow regression on all settings sub-tabs

### Story 04 — Settings subsubsub tabs

- Added `get_settings_tab_slugs()`, `get_current_settings_tab()`, `render_settings_sub_tabs()` to admin class
- Replaced custom button nav with WordPress `subsubsub` links using `ctc-tab` query parameter (legacy `tab` param still accepted)
- Server-side active panel via PHP; all panels remain in DOM for full save behavior
- Removed `initTabs()` and settings nav CSS from admin JS/CSS
- Save redirect updated to use `ctc-tab`
- Browser QA: General and Exclusions tabs verified on pre-prod

### Story 05 — form-table layout

- Migrated General, Button, Display, and Exclusions tabs from card/grid layout to WordPress `form-table` + `h2.title` sections
- Save button moved to native `<p class="submit">`
- Position toggle rows use `<tr class="ctc-chat-*-position-row">` for JS show/hide

### Story 06 — Product meta box

- Cleaned up `render_product_meta_box()` with proper label associations and `widefat` controls
- Added scoped CSS for `#ctc_chat_product_settings`
- Browser QA: meta box renders on variable product edit screen (post 686)

## Remaining Work

| Story | Title | Status |
|-------|-------|--------|
| 01–08 | Admin native redesign | **Complete** |

Optional non-blocking follow-up: simple product meta save/reload QA on product edit screen.

## Final QA

See `docs/evidence/admin-native-redesign/final-qa.md` — **PASS** for admin transformation release.

## Commands Run

```bash
php -l admin/class-ctc-chat-admin.php
# Pass

# Visual QA via in-app browser on all four admin routes — pass (desktop)
```

## Risks Before Release

1. Settings sub-tab JS refactor may affect save flow — needs dedicated QA after Story 04.
2. Exclusions tab not validated until native sub-tab routing ships.
3. Product meta box still uses legacy styling — may confuse merchants until Story 06.
