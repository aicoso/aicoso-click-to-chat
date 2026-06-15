# Story 04: Settings Sub-Tabs To WordPress subsubsub

## Description

Replace the custom JavaScript-driven settings sub-tab buttons (`.ctc-chat-settings-nav-item`) with WordPress-native `subsubsub` navigation, matching Affiliate Program settings tab behavior.

## Acceptance Criteria

- Settings page sub-tabs use `<ul class="subsubsub">` with standard WordPress tab links.
- Active sub-tab is determined by URL query parameter (e.g. `ctc-tab=general`, `ctc-tab=display`, `ctc-tab=exclusions`).
- Custom `.ctc-chat-settings-nav-item` buttons and associated JS tab-switching handlers are removed.
- Each sub-tab panel renders server-side (no client-side show/hide of entire form sections unless required for progressive disclosure).
- Exclusions tab content is reachable and functional.
- Keyboard navigation and focus states follow WordPress admin conventions.
- No console errors on tab switch.

## Technical Details

**Files to modify**

- `admin/views/settings-page.php`
- `admin/class-ctc-chat-admin.php` (tab routing if needed)
- `admin/js/admin.js` (remove legacy nav-item handlers)
- `admin/css/admin.css` (remove `.ctc-chat-settings-nav-*` rules)

**Suggested sub-tab slugs**

| Slug | Label |
|------|-------|
| `general` | General |
| `display` | Display |
| `exclusions` | Exclusions |

**Reference pattern**

```php
<ul class="subsubsub">
    <li><a href="..." class="current">General</a> |</li>
    <li><a href="...">Display</a> |</li>
    <li><a href="...">Exclusions</a></li>
</ul>
```

## Dev Notes

- Reference: Affiliate Program Story 17 (native settings screen) and Story 26 (clean settings tabs).
- Depends on: Stories 01, 02, 03.
- Blocks: Story 07 (full QA).
- **Status (2026-06-12):** Implemented. Settings sub-tabs use `subsubsub` via `render_settings_sub_tabs()` with `ctc-tab` query parameter. Legacy button nav and JS handlers removed.
