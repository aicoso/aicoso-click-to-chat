# Research: Click to Chat Admin Baseline

**Feature**: 001-admin-native-redesign  
**Date**: 2026-06-12

## Baseline Inventory (Pre-Transformation)

### Admin pages

| Page slug | Menu label | View file |
|-----------|------------|-----------|
| `click-to-chat` | Click to Chat | `settings-page.php` |
| `click-to-chat-numbers` | WhatsApp Numbers | `numbers-page.php` |
| `click-to-chat-templates` | Message Templates | `templates-page.php` |
| `click-to-chat-shortcodes` | Shortcode | `shortcode-page.php` |

### Legacy styling patterns (removed or targeted)

- Purple linear-gradient page headers (`.ctc-chat-admin-header`)
- Custom pill/tab buttons for cross-page and settings navigation
- Custom warning boxes (`.ctc-chat-warning-box`)
- Dashicon WhatsApp menu icon
- Unscoped Select2 overrides
- Shortcode page emoji/purple banner

### Reference target (Affiliate Program)

- Shared product header with Aicoso logo + breadcrumb
- Cross-page tabs via `.apf-admin-tabs` / `.ctc-admin-tabs` equivalent
- Settings sub-tabs via `subsubsub`
- Design tokens under `:root` with `--apf-admin-*` prefix
- Native `.notice` components
- Evidence-driven story workflow under `stories/` and `specs/`

## Completed Work (as of 2026-06-12)

Stories 01–03 implemented:

- Shell helpers in `class-ctc-chat-admin.php`
- Aicoso logo SVG assets copied
- Admin CSS token system (`--ctc-admin-*`)
- View templates stripped of legacy chrome
- Visual QA pass on all four pages (desktop, no console errors)

## Remaining Gaps

1. Settings sub-tabs still use `.ctc-chat-settings-nav-item` + JS
2. Settings form uses `.ctc-chat-form-group` instead of `form-table`
3. Product meta box not yet aligned to Aicoso pattern
4. Residual `#25D366` in admin CSS outside preview contexts
5. Mobile (782px) and Exclusions tab QA not complete
6. Evidence folder needs final QA artifacts

## Risks

| Risk | Mitigation |
|------|------------|
| Settings tab JS refactor breaks save flow | Server-side tab routing + preserve form field names |
| Select2 re-init on tab change | Single-page load per sub-tab via URL |
| Product meta regression | QA on simple + variable products before release |

## Assumptions

- Pre-prod Studio site at `http://localhost:8882` remains available for browser QA.
- No option key or post meta renames during UI-only transformation.
