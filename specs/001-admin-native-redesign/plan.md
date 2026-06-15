# Implementation Plan: Click to Chat Admin Native Redesign

**Branch**: `codex/click-to-chat-admin-native-redesign` | **Date**: 2026-06-12 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/001-admin-native-redesign/spec.md`

## Summary

Transform Aicoso Click to Chat admin UI from legacy custom styling (purple gradients, pill buttons, JS-driven settings tabs) to the WordPress-native Aicoso admin pattern established in Affiliate Program for WooCommerce. Work introduces a shared admin shell, design tokens, view template cleanup, native settings tabs, form-table migration, product meta box polish, JS cleanup, and release evidence.

## Technical Context

**Language/Version**: PHP for WordPress plugin development. Validate against WordPress Studio pre-prod site with WooCommerce active.

**Primary Dependencies**: WordPress admin APIs, WooCommerce product meta APIs, Select2 (existing), scoped admin CSS, small vanilla admin JS.

**Storage**: Existing WordPress options (`ctc_chat_*` settings), post meta (`_ctc_chat_*`), no schema migration planned.

**Testing**: `php -l` for changed PHP files, `rg` absence checks for legacy CSS classes, browser QA at `http://localhost:8882/`, evidence under `docs/evidence/admin-native-redesign/`.

**Target Platform**: WordPress admin for WooCommerce stores; public WhatsApp button behavior preserved.

**Project Type**: WooCommerce extension plugin (`aicoso-click-to-chat`).

**Performance Goals**: Admin pages render final UI on first paint; no post-render DOM replacement for shell chrome; Select2 scoped and initialized once per page.

**Constraints**: Preserve existing admin page slugs, option keys, post meta keys, shortcodes, and storefront button logic. Use Dashicons only in admin (menu uses Aicoso SVG). Match Affiliate Program header/tab pattern without copying unrelated report/dashboard code.

**Scale/Scope**: Four admin pages, product meta box, admin CSS/JS, release evidence.

## Constitution Check

- **Store Compatibility**: Admin URLs, settings keys, post meta, and shortcodes preserved.
- **WordPress Native Architecture**: PHP-rendered admin shell, scoped enqueues, plugin-owned CSS, no external fonts/icons in admin.
- **Security and Privacy**: Existing capability checks and nonces preserved; escaped output in new shell renderers.
- **Incremental Verification**: Stories 01–08 independently verifiable with syntax and browser checks.
- **Release Evidence**: Route checklist and implementation notes required before completion.

No constitution violations identified.

## Project Structure

### Documentation (this feature)

```text
specs/001-admin-native-redesign/
├── spec.md
├── plan.md
├── research.md
├── quickstart.md
├── checklists/requirements.md
├── contracts/admin-ui-contract.md
└── tasks.md

stories/
├── story-01-native-admin-shell-and-route-structure.md
├── story-02-admin-css-design-system-cleanup.md
├── story-03-view-template-migration-and-native-notices.md
├── story-04-settings-sub-tabs-to-subsubsub.md
├── story-05-form-table-native-form-layout.md
├── story-06-product-meta-box-and-edge-admin-surfaces.md
├── story-07-js-select2-alignment-and-dead-code-removal.md
└── story-08-final-browser-qa-and-release-evidence.md
```

### Source Code

```text
aicoso-click-to-chat.php
admin/
├── class-ctc-chat-admin.php
├── css/admin.css
├── js/admin.js
├── images/
│   ├── aicoso-main-logo.svg
│   └── aicoso-admin-menu-logo.svg
└── views/
    ├── settings-page.php
    ├── numbers-page.php
    ├── templates-page.php
    └── shortcode-page.php
docs/evidence/admin-native-redesign/
```

**Structure Decision**: Keep existing plugin layout. Add stories, specs, and evidence folders mirroring Affiliate Program workflow. Shell renderers live in `admin/class-ctc-chat-admin.php`.

## Implementation Phases

| Phase | Stories | Status (2026-06-12) |
|-------|---------|---------------------|
| 1 — Shell + assets | 01 | Done |
| 2 — CSS tokens + cleanup | 02 | Done |
| 3 — View migration | 03 | Done |
| 4 — Settings sub-tabs | 04 | Done |
| 5 — form-table layout | 05 | Done |
| 6 — Product meta box | 06 | Done |
| 7 — JS cleanup | 07 | Done |
| 8 — Final QA + evidence | 08 | Done |

## Reference Implementation

Affiliate Program for WooCommerce in the same pre-prod site:

- `admin/class-affiliate-program-for-woocommerce-admin.php` — shell renderers
- `admin/css/affiliate-program-for-woocommerce-admin.css` — `--apf-admin-*` tokens
- `stories/story-02-native-admin-shell-and-route-structure.md`
- `stories/story-08-admin-css-design-system-cleanup.md`
- `stories/story-22-reusable-product-header-and-tabs-for-settings-and-reports.md`
