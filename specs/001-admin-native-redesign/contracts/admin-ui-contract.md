# Admin UI Contract

## Preserved Routes

- `admin.php?page=click-to-chat` (Settings)
- `admin.php?page=click-to-chat-numbers` (WhatsApp Numbers)
- `admin.php?page=click-to-chat-templates` (Message Templates)
- `admin.php?page=click-to-chat-shortcodes` (Shortcode)

### Settings sub-tabs (after Story 04)

- `admin.php?page=click-to-chat&ctc-tab=general`
- `admin.php?page=click-to-chat&ctc-tab=display`
- `admin.php?page=click-to-chat&ctc-tab=exclusions`

## Rendering Contract

- All admin pages render inside `.ctc-wrap` via shared shell helpers.
- View templates MUST NOT contain standalone page headers or duplicate cross-page tab bars.
- Shell renderers MUST escape text, attributes, and URLs at output.
- Setup/status messages MUST use WordPress-native `.notice` markup.

## Header And Tab Contract

- Product header includes Aicoso logo (`aicoso-main-logo.svg`), product title, and breadcrumb.
- Cross-page tabs use `.ctc-admin-tabs` with four tabs: Settings, Numbers, Templates, Shortcode.
- Active tab state MUST match current admin page slug.
- Settings sub-tabs MUST use `<ul class="subsubsub">` (not custom button nav).

## Asset Contract

- Admin scripts/styles enqueued through WordPress admin hooks only.
- Menu icon uses `aicoso-admin-menu-logo.svg`.
- Dashicons allowed for inline admin icons; no external icon libraries.
- No external fonts in admin CSS.
- Select2 styling scoped under `.ctc-wrap` only.

## CSS Contract

- Design tokens use `--ctc-admin-*` prefix under `:root`.
- Legacy classes MUST NOT appear in rendered DOM after transformation:
  - `.ctc-chat-admin-header`
  - `.ctc-chat-warning-box`
  - `.ctc-chat-settings-nav-item`
- Buttons use WordPress classes: `button`, `button-primary`, `button-secondary`.

## Data Contract

- Settings option keys (`ctc_chat_*`) MUST NOT be renamed during UI transformation.
- Product post meta keys (`_ctc_chat_hide_button`, `_ctc_chat_assigned_number`, `_ctc_chat_custom_message`) MUST be preserved.
- Shortcode behavior MUST remain unchanged.
- Save flows MUST retain existing nonce and capability checks.

## Product Meta Box Contract

- Meta box renders on WooCommerce product edit screens.
- Fields: hide button toggle, assigned number select, custom message textarea.
- Save/load behavior unchanged; styling aligned to native WordPress meta box pattern.
