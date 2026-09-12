# Quickstart: Click to Chat Admin Native Redesign Verification

## Preconditions

- Local WordPress Studio pre-prod site is available at `http://localhost:8882/` when browser QA is possible.
- WooCommerce is active for product meta box validation.
- Plugin path: `sites/pre-prod-enviroment-aicoso/wp-content/plugins/aicoso-click-to-chat/`

## Code Checks

```bash
cd sites/pre-prod-enviroment-aicoso/wp-content/plugins/aicoso-click-to-chat

php -l admin/class-ctc-chat-admin.php
find admin/views -name '*.php' -print -exec php -l {} \;

# Legacy patterns that should be absent after full transformation
rg "ctc-chat-admin-header|linear-gradient|ctc-chat-settings-nav-item|ctc-chat-warning-box" admin/
```

## Browser Checks

Visit these routes:

- Settings: `/wp-admin/admin.php?page=click-to-chat`
- WhatsApp Numbers: `/wp-admin/admin.php?page=click-to-chat-numbers`
- Message Templates: `/wp-admin/admin.php?page=click-to-chat-templates`
- Shortcode: `/wp-admin/admin.php?page=click-to-chat-shortcodes`
- Settings General: `/wp-admin/admin.php?page=click-to-chat&ctc-tab=general` (after Story 04)
- Settings Display: `/wp-admin/admin.php?page=click-to-chat&ctc-tab=display` (after Story 04)
- Settings Exclusions: `/wp-admin/admin.php?page=click-to-chat&ctc-tab=exclusions` (after Story 04)

Confirm:

- Aicoso logo, breadcrumb, and cross-page tabs render on every page.
- Active cross-page tab matches current route.
- Setup notices use native `.notice` markup.
- No purple gradient banners or `.ctc-chat-admin-header` elements in DOM.
- Sidebar menu shows Aicoso SVG icon for Click to Chat.
- Settings sub-tabs use `subsubsub` (after Story 04).
- No page-level horizontal scroll on desktop (1905px) or mobile (782px).
- No console errors on any admin page.

## Product Meta Box Check

- Open a product edit screen and confirm Click to Chat meta box renders with native styling.
- Save hide/number/message overrides and verify storefront behavior.

## Evidence

Save QA notes under:

```text
docs/evidence/admin-native-redesign/
```

Expected files:

- `README.md`
- `implementation-notes.md`
- `final-qa.md` (after Story 08)
