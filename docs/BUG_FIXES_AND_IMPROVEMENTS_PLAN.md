# Implementation Plan: Bug Fixes and Enhancements for AICOSO Click to Chat

This document details the complete root cause analysis, file-by-file changes, and implementation plan for resolving all 11 audited bugs and enhancements in the `aicoso-click-to-chat` WordPress plugin.

---

## 1. Executive Summary of Issues

| # | Issue | Severity | Component | Key Symptom |
|---|---|---|---|---|
| **1** | WhatsApp Newlines Stripped | **High (UX)** | Frontend Link Renderer | WhatsApp message arrives as a single run-on sentence. |
| **2** | Multi-Number Routing Selects Wrong Number | **High (Business Logic)** | Routing & Admin Settings | 2nd number receives messages instead of the default number. |
| **3** | Fatal Error Risk on `$product->get_id()` | **High (Stability)** | Single Product Hook | Uncaught Error calling method on null if WooCommerce globals desync. |
| **4** | Missing "Shop" Template in Admin UI | **Medium** | Admin Templates View | Saving templates wipes out the `shop` template configuration. |
| **5** | Stored XSS in Analytics Reports Table | **High (Security)** | Admin Analytics JS | Untrusted product names and URLs rendered via `innerHTML`. |
| **6** | Variable Product Variation ID Never Captured | **Medium** | Public Frontend JS | Analytics `variation_id` column always records `NULL`. |
| **7** | No Pagination Controls on Analytics Reports | **Medium** | Admin Reports UI & JS | Hardcoded `page: 1`; records beyond page 1 are unreachable. |
| **8** | Missing Post-Redirect-Get (PRG) Pattern | **Medium** | Admin Controller | Browser refresh causes form re-submission warnings. |
| **9** | Uninstaller Leaves Migration Artifacts | **Low** | Cleanup (`uninstall.php`) | Leftover schema version options in `wp_options`. |
| **10** | Hardcoded SVG Icons Missing Alt/Aria Labels | **Low (A11y)** | Frontend Renderer | Screen readers lack accessibility descriptions for chat icon. |
| **11** | Redundant Settings Migration Runs | **Low (Perf)** | Core Loader | Settings migration runs repeatedly when version check condition lacks guard. |

---

## 2. Deep Dive & Root Cause Analysis

### Issue 1: WhatsApp Newline Stripping
- **Root Cause**: In `includes/class-ctc-chat-button-renderer.php` (line 69), the generated link is output with:
  ```php
  <a href="<?php echo esc_url( $url ); ?>">
  ```
  WordPress core's `esc_url()` in `wp-includes/formatting.php` (lines 4565–4568) explicitly strips `%0a`, `%0d`, `%0A`, `%0D` from URL query parameters. This strips out all line break encodings. Furthermore, Windows `<textarea>` inputs submit `\r\n`, which encodes to `%0D%0A`.
- **Target Resolution**:
  1. In `includes/class-ctc-chat-whatsapp-link-generator.php`, normalize all line breaks (`\r\n`, `\r`) to `\n` and decode HTML entities before applying `rawurlencode()`.
  2. In `includes/class-ctc-chat-button-renderer.php`, protect `%0A` by substituting a temporary placeholder token (`__CTC_NL__`), running `esc_url()`, and substituting `%0A` back into the final sanitized URL.

---

### Issue 2: Multi-Number Routing Bug
- **Root Cause 1 (Missing Default on Initial Save)**: In `admin/views/numbers-page.php` (line 90), the default checkbox `<input type="checkbox" name="ctc_numbers[0][is_default]">` is unchecked unless explicitly ticked.
- **Root Cause 2 (Fallback Exclusion)**: In `includes/class-ctc-chat-whatsapp-link-generator.php` (lines 141–158), Step 2 fallback searches for an unassigned number. If Number 1 has any assignment, it is excluded; the newly added Number 2 (unassigned) is picked as fallback.
- **Root Cause 3 (ID Name Typo)**: In `admin/views/numbers-page.php` (line 153), the hidden ID input uses `name="ctc_chat_numbers[...][id]"` instead of `ctc_numbers[...][id]`. This causes `$_POST['ctc_numbers']` to lack IDs, forcing ID regeneration and breaking all existing postmeta assignments.
- **Target Resolution**:
  1. Fix the input name typo to `ctc_numbers[...][id]`.
  2. In `ctc_chat_normalize_number_record_ids()`, ensure the first number is guaranteed `is_default = true` if no number has `is_default` set.
  3. In `get_whatsapp_number()`, if neither explicit assignment nor fallback matches, fall back to the first number in the list.

---

### Issue 3: Null Product Handling
- **Root Cause**: In `includes/class-ctc-chat-button-display.php` (lines 244–248 and 299–308):
  ```php
  $product_id = $product->get_id(); // Called before checking if ( ! $product )
  if ( ! $product ) { return; }
  ```
- **Target Resolution**: Relocate `$product_id = $product->get_id()` after the `if ( ! $product || ! is_a( $product, 'WC_Product' ) )` guard check.

---

### Issue 4: Missing Shop Template in Admin UI
- **Root Cause**: `admin/views/templates-page.php` only renders cards for `single_product` and `general`. When the form posts, `save_templates()` in `admin/class-ctc-chat-admin.php` replaces `$settings['message_templates']` with only the posted keys, permanently erasing `shop`.
- **Target Resolution**: Add a third template card for the `shop` page in `templates-page.php`, and preserve existing keys in `save_templates()`.

---

### Issue 5: Stored XSS in Analytics Reports
- **Root Cause**: In `admin/js/analytics-reports.js` (lines 34–39):
  ```javascript
  const productHtml = row.product_id ? `<a href="${row.page_url}">${row.product_name}</a>` : '—';
  ```
  Values are injected into table rows via `innerHTML` without escaping HTML entities.
- **Target Resolution**: Implement an `escapeHtml()` helper function in `analytics-reports.js` and wrap all dynamic table values before building the DOM string.

---

### Issue 6: Variable Product Variation ID Not Captured
- **Root Cause**: In `public/js/public.js` (line 127), `data-ctc-variation-id` is never updated when WooCommerce triggers `found_variation` or `reset_data`.
- **Target Resolution**: In `public.js`, listen to `$(document).on('found_variation reset_data', ...)` and update `button.dataset.ctcVariationId = variation.variation_id` and recalculate the message URL dynamically if variation placeholders are present.

---

### Issue 7: Analytics Reports Pagination
- **Root Cause**: `admin/views/reports-page.php` has no pagination markup, and `analytics-reports.js` hardcodes `page: 1`.
- **Target Resolution**: Add Prev/Next pagination buttons and page indicator controls to `reports-page.php`, and track `currentPage` and total pages in `analytics-reports.js`.

---

### Issue 8: Post-Redirect-Get (PRG) Pattern
- **Root Cause**: `save_numbers()` and `save_templates()` in `admin/class-ctc-chat-admin.php` handle `$_POST` inline during the page render hook without a `wp_safe_redirect()`.
- **Target Resolution**: Hook form processing into `admin_init` or call `wp_safe_redirect( add_query_arg( 'updated', 'true', wp_get_referer() ) )` followed by `exit;`.

---

### Issue 9: Cleanup on Uninstallation
- **Root Cause**: `uninstall.php` deletes `ctc_chat_settings` and the analytics table, but leaves `ctc_chat_settings_schema_version` and `ctc_chat_settings_migration_error`.
- **Target Resolution**: Add `delete_option( 'ctc_chat_settings_schema_version' )` and `delete_option( 'ctc_chat_settings_migration_error' )` to `uninstall.php`.

---

## 3. Proposed Code Changes by File

### 1. `includes/class-ctc-chat-whatsapp-link-generator.php`
- Normalize line endings: `$message = str_replace( array( "\r\n", "\r" ), "\n", $message );`
- Decode entities: `$message = html_entity_decode( $message, ENT_QUOTES | ENT_HTML5, 'UTF-8' );`
- Fallback number: if Step 2 returns empty, return `$numbers[0]['number']`.

### 2. `includes/class-ctc-chat-button-renderer.php`
- Wrap `esc_url()`:
  ```php
  $safe_placeholder = '__CTC_NEWLINE_TOKEN__';
  $url_with_token   = str_replace( array( '%0A', '%0a' ), $safe_placeholder, $url );
  $escaped_url      = esc_url( $url_with_token );
  $final_url        = str_replace( $safe_placeholder, '%0A', $escaped_url );
  ```

### 3. `admin/views/numbers-page.php`
- Fix hidden ID input name: Change `name="ctc_chat_numbers[<?php echo esc_attr( $index ); ?>][id]"` to `name="ctc_numbers[<?php echo esc_attr( $index ); ?>][id]"`.
- Check default box by default on card 0 if no other number is default.

### 4. `includes/class-ctc-chat-analytics-helpers.php`
- In `ctc_chat_normalize_number_record_ids()`, if array is non-empty and no record has `is_default === true`, set `$numbers[0]['is_default'] = true`.

### 5. `includes/class-ctc-chat-button-display.php`
- Move `$product_id = $product->get_id();` below the null check:
  ```php
  if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
      return;
  }
  $product_id = $product->get_id();
  ```

### 6. `admin/views/templates-page.php` & `admin/class-ctc-chat-admin.php`
- Add the `shop` template card with tokens `{site_title}`, `{shop_url}`.
- Merge posted templates with existing stored templates so unspecified keys are preserved.

### 7. `admin/js/analytics-reports.js` & `admin/views/reports-page.php`
- Add `escapeHtml( str )` function and sanitize all rendered values.
- Add pagination footer with Prev/Next buttons and page numbers.

### 8. `public/js/public.js`
- Bind WooCommerce variation events:
  ```javascript
  $( document ).on( 'found_variation', 'form.variations_form', function( event, variation ) {
      const btn = document.querySelector( '.ctc-chat-btn[data-ctc-product-id]' );
      if ( btn && variation && variation.variation_id ) {
          btn.dataset.ctcVariationId = variation.variation_id;
      }
  } );
  ```

### 9. `uninstall.php`
- Add missing option keys to `uninstall.php`:
  ```php
  delete_option( 'ctc_chat_settings_schema_version' );
  delete_option( 'ctc_chat_settings_migration_error' );
  ```

---

## 4. Verification Plan

### Automated Checks
- Run PHP lint check on all modified files:
  ```powershell
  & "C:\Users\Adarsh\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe" -l <file>
  ```
- Run PHP_CodeSniffer for WordPress standards:
  ```powershell
  ./vendor/bin/phpcs
  ```

### Manual Verification
1. **WhatsApp Newlines**: Send a message with 3+ lines. Click button on desktop and mobile. Confirm WhatsApp Web and mobile app open with exact line breaks intact.
2. **Multi-Number Routing**: Configure Number 1 and Number 2. Mark Number 1 as Default. Verify message routes to Number 1 on unassigned products and pages.
3. **Variable Products**: Select a variation on a WooCommerce variable product; click chat button; check `wp_ctc_chat_clicks` database table to verify `variation_id` is recorded correctly.
4. **XSS Security Check**: Create a test product with `<script>alert('XSS')</script>`. Click button. Open Analytics Reports in WP Admin and verify no script executes.
5. **Shop Template**: Save templates in Admin. Verify `shop` template value is saved and not deleted.
