# Story 01: Native Admin Shell And Route Structure

## Description

Replace the legacy custom admin page wrappers with a WordPress-native admin shell for all Click to Chat admin routes. Introduce shared shell open/close helpers, Aicoso product header, breadcrumb, and cross-page tab navigation aligned with Affiliate Program for WooCommerce.

## Acceptance Criteria

- All four admin pages render inside a shared `.ctc-wrap` shell.
- Each page shows the Aicoso logo, product title, breadcrumb, and cross-page tabs.
- Legacy per-view page headers, purple gradient banners, and duplicate tab markup are removed from view templates.
- Admin menu uses the Aicoso SVG menu icon instead of a dashicon.
- Setup status notices render as native `.notice` components inside the shell.
- No horizontal page-level scroll appears on desktop at 1905px width.
- PHP syntax checks pass for `admin/class-ctc-chat-admin.php` and all view templates.

## Technical Details

**Files to modify**

- `admin/class-ctc-chat-admin.php`
- `admin/views/settings-page.php`
- `admin/views/numbers-page.php`
- `admin/views/templates-page.php`
- `admin/views/shortcode-page.php`
- `admin/images/aicoso-main-logo.svg` (copy from Affiliate Program)
- `admin/images/aicoso-admin-menu-logo.svg` (copy from Affiliate Program)

**New methods in admin class**

- `get_logo_url()`
- `get_menu_icon_url()`
- `render_product_header( $breadcrumb )`
- `render_admin_tabs( $active_tab )`
- `render_setup_status_notices()`
- `render_admin_shell_open( $active_tab, $breadcrumb )`
- `render_admin_shell_close()`

**Cross-page tabs**

| Tab slug | Label | Route |
|----------|-------|-------|
| `settings` | Settings | `admin.php?page=click-to-chat` |
| `numbers` | WhatsApp Numbers | `admin.php?page=click-to-chat-numbers` |
| `templates` | Message Templates | `admin.php?page=click-to-chat-templates` |
| `shortcode` | Shortcode | `admin.php?page=click-to-chat-shortcodes` |

## Dev Notes

- Reference implementation: Affiliate Program `render_product_header()`, `render_admin_tabs()`, `render_admin_shell_open()`.
- Depends on: none (first story in the transformation).
- Blocks: Stories 02–07.
- **Status (2026-06-12):** Implemented and visually verified on pre-prod (`http://localhost:8882`).
