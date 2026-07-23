# Settings Update Preservation Evidence

## Scope

This evidence package validates the `ctc_chat_settings` migration for supported source rows
R1 (1.0.0), R2 (1.0.1), and R3 (1.0.2), plus missing, malformed, and failed-write states.

## Environment

- WordPress: record tested version
- WooCommerce: record tested version
- PHP: record tested version
- Source rows: R1-R3
- Target settings schema: 1.0.0

## Commands and Routes

- WP-CLI fixture command from `specs/003-fix-settings-reset/quickstart.md`
- Admin settings route and storefront journeys listed in `browser-qa.md`

## Results

Record fixture output, browser results, write counts, settings hashes, unresolved risks, and
release recommendation in `fixture-results.md` and `browser-qa.md`.