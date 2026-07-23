# Data Model: Settings Update Preservation

## 1. Saved Plugin Configuration

**Storage identity**: Existing `ctc_chat_settings` option.

**Role**: Authoritative merchant configuration and compatibility container.

### Canonical top-level fields

| Field | Type | Validation / preservation rule |
|-------|------|--------------------------------|
| `plugin_enabled` | boolean | Existing false is authoritative |
| `whatsapp_numbers` | list | Preserve order, identifiers, values, and assignments |
| `button_settings` | object | Preserve text, icon flag, and colors |
| `single_product` | object | Preserve enabled flag and position |
| `shop_page` | object | Preserve enabled flag and position |
| `cart_page` | object | Preserve enabled flag and position |
| `checkout_page` | object | Preserve enabled flag and position |
| `thankyou_page` | object | Preserve enabled flag |
| `floating_button` | object | Preserve enabled flag and position |
| `message_templates` | object | Preserve multiline and intentionally blank strings |
| `exclusions` | object | Preserve empty and populated ID lists |
| `advanced` | object | Preserve catalog and hide flags |
| `analytics` | object | Add defaults only for absent fields |
| unknown fields | any | Retain unchanged |

### Legacy-to-canonical top-level mapping

| Legacy field | Canonical field |
|--------------|-----------------|
| `ctc_chat_plugin_enabled` | `plugin_enabled` |
| `ctc_chat_whatsapp_numbers` | `whatsapp_numbers` |
| `ctc_chat_button_settings` | `button_settings` |
| `ctc_chat_single_product` | `single_product` |
| `ctc_chat_shop_page` | `shop_page` |
| `ctc_chat_cart_page` | `cart_page` |
| `ctc_chat_checkout_page` | `checkout_page` |
| `ctc_chat_thankyou_page` | `thankyou_page` |
| `ctc_chat_floating_button` | `floating_button` |
| `ctc_chat_message_templates` | `message_templates` |
| `ctc_chat_exclusions` | `exclusions` |
| `ctc_chat_advanced` | `advanced` |

## 2. Nested Field Mappings

### Button settings

| Legacy | Canonical |
|--------|-----------|
| `ctc_chat_text` | `text` |
| `ctc_chat_icon` | `icon` |
| `ctc_chat_bg_color` | `bg_color` |
| `ctc_chat_text_color` | `text_color` |

### Placement groups

Applies to single product, shop, cart, checkout, thank-you, and floating groups where the
field exists.

| Legacy | Canonical |
|--------|-----------|
| `ctc_chat_enabled` | `enabled` |
| `ctc_chat_position` | `position` |

### WhatsApp number record

| Legacy | Canonical |
|--------|-----------|
| `ctc_chat_id` | `id` |
| `ctc_chat_name` | `name` |
| `ctc_chat_number` | `number` |
| `ctc_chat_description` | `description` |
| `ctc_chat_is_default` | `is_default` |
| `ctc_chat_assignments` | `assignments` |

### Number assignments

| Legacy | Canonical |
|--------|-----------|
| `ctc_chat_products` | `products` |
| `ctc_chat_categories` | `categories` |
| `ctc_chat_pages` | `pages` |

### Message templates

| Legacy | Canonical |
|--------|-----------|
| `ctc_chat_single_product` | `single_product` |
| `ctc_chat_cart_checkout` | `cart_checkout` |
| `ctc_chat_thank_you` | `thank_you` |
| `ctc_chat_floating` | `floating` |
| `ctc_chat_variations` | `variations` |

The canonical `shop` template has no released legacy equivalent and receives its default
only when absent.

### Exclusions

| Legacy | Canonical |
|--------|-----------|
| `ctc_chat_pages` | `pages` |
| `ctc_chat_posts` | `posts` |
| `ctc_chat_categories` | `categories` |
| `ctc_chat_tags` | `tags` |
| `ctc_chat_products` | `products` |

### Advanced settings

| Legacy | Canonical |
|--------|-----------|
| `ctc_chat_hide_add_to_cart` | `hide_add_to_cart` |
| `ctc_chat_hide_proceed_checkout` | `hide_proceed_checkout` |
| `ctc_chat_hide_place_order` | `hide_place_order` |
| `ctc_chat_catalog_mode` | `catalog_mode` |

## 3. Settings Schema Version

**Storage identity**: `ctc_chat_settings_schema_version`.

| Field | Type | Rule |
|-------|------|------|
| version | string | Written only after successful settings persistence |

The initial canonical migration target is `1.0.0`. It is independent of the plugin version
and analytics database version.

## 4. Migration Error Marker

**Storage identity**: `ctc_chat_settings_migration_error`.

| Field | Type | Rule |
|-------|------|------|
| `code` | string | Non-sensitive stable error identifier |
| `source_schema` | string | `missing`, `legacy`, `canonical`, `mixed`, or `malformed` |
| `target_schema` | string | Intended settings schema version |
| `recorded_at` | string | Site-time timestamp |

The marker MUST NOT contain the settings payload, phone numbers, templates, assignments,
URLs, or other merchant data. It is cleared after a later successful migration.

## 5. State Transitions

| Initial state | Transition | Result |
|---------------|------------|--------|
| Option missing | Install defaults | Canonical defaults + schema version |
| Legacy only | Map, remove recognized legacy paths, then complete defaults | Canonical-only recognized values + unknown fields + version |
| Canonical only, unversioned | Complete absent defaults | Canonical values retained + version |
| Mixed, unversioned | Canonical precedence; legacy fills gaps; remove mapped legacy paths | Canonical-only recognized values + unknown fields + version |
| Current and versioned | Compare fast path | No write when unchanged |
| Malformed | Abort safely | Original untouched + diagnostic marker |
| Prior failure corrected | Retry migration | Marker cleared after successful write |

## 6. Invariants

1. Absence is determined with strict key existence, never truthiness.
2. Migration removes only recognized mapped legacy paths after their logical values exist
   canonically. If a mapped legacy container contains unknown children, its shell is retained
   with those children; unknown or extension-owned paths are never deleted.
3. List order is stable.
4. Unknown nested fields remain attached to their containing records.
5. Reapplying migration to a converged record produces an identical value and no option write.
6. The schema version never advances if the settings write fails or input is malformed.
7. A successfully persisted record contains one copy of each recognized logical setting.
