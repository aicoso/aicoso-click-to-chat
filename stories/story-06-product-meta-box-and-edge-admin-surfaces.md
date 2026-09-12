# Story 06: Product Meta Box And Edge Admin Surfaces

## Description

Apply the Aicoso admin design pattern to the WooCommerce product meta box and any remaining edge admin surfaces (order screens, plugin list row meta, inline admin notices outside the main shell).

## Acceptance Criteria

- Product edit screen meta box uses native WordPress meta box styling without purple/green custom chrome.
- Meta box fields (`Hide button`, `Assigned number`, `Custom message`) remain wired to existing save/load logic.
- Toggle and select controls in the meta box match admin token styling where scoped.
- No regression in variable product override behavior on the storefront.
- Plugin action links and admin footer text (if any) remain unchanged unless explicitly in scope.

## Technical Details

**Files to modify**

- `admin/class-ctc-chat-admin.php` (`render_product_meta_box()`)
- `admin/css/admin.css` (scoped meta box rules under `.postbox` or `#ctc-chat-product-meta`)

**Out of scope**

- Frontend button display CSS (`public/css/`)
- Public JavaScript behavior

## Dev Notes

- Reference: Affiliate Program product-level admin surfaces (if any) and WordPress core meta box conventions.
- Depends on: Story 02 (design tokens).
- Blocks: Story 07.
- **Status (2026-06-12):** Implemented. Meta box uses native side-panel markup, corrected label `for` attributes, and scoped CSS under `#ctc_chat_product_settings`.
