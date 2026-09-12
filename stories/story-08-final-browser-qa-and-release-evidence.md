# Story 08: Final Browser QA And Release Evidence

## Description

Perform final browser and WP-CLI validation for the full Click to Chat admin transformation and save implementation evidence under `docs/evidence/admin-native-redesign/`.

## Acceptance Criteria

- Settings page loads with Aicoso header, breadcrumb, cross-page tabs, and native notices.
- WhatsApp Numbers page loads and add/edit/delete flows work.
- Message Templates page loads and save flow works.
- Shortcode page loads with native layout (no legacy banner).
- Settings sub-tabs (General, Display, Exclusions) work via `subsubsub` navigation.
- Product meta box renders with native styling on product edit screen.
- No page-level horizontal scroll on desktop (1905px) or mobile (782px).
- No console errors on any admin page.
- PHP syntax checks pass for all admin PHP files.
- Evidence file exists with screenshots and validation notes.

## Technical Details

**Suggested checks**

```bash
php -l admin/class-ctc-chat-admin.php
find admin/views -name '*.php' -print -exec php -l {} \;
rg "ctc-chat-admin-header|linear-gradient|ctc-chat-settings-nav-item" admin/
```

**Browser routes**

- Settings: `/wp-admin/admin.php?page=click-to-chat`
- Numbers: `/wp-admin/admin.php?page=click-to-chat-numbers`
- Templates: `/wp-admin/admin.php?page=click-to-chat-templates`
- Shortcode: `/wp-admin/admin.php?page=click-to-chat-shortcodes`
- Product edit: `/wp-admin/post.php?post=<product-id>&action=edit`

**Confirm**

- DOM contains `.ctc-wrap`, `.ctc-product-header`, `.ctc-admin-tabs`.
- No purple gradient banners or `.ctc-chat-admin-header` elements.
- Sidebar menu shows Aicoso SVG icon for Click to Chat.

## Dev Notes

- Last story in the transformation.
- Depends on all implementation stories (01–07).
- If any acceptance criterion fails, open a follow-up bug before merge/release.
- **Status (2026-06-12):** Complete. Evidence in `docs/evidence/admin-native-redesign/final-qa.md`.
