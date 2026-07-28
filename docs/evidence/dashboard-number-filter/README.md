# Dashboard WhatsApp Number Filter Evidence

Status: Automated validation passed; browser QA and repository-wide PHPCS are
blocked as described below.

## Environment

- WordPress: 7.0.2
- WooCommerce: 10.4.4
- PHP: 8.2.12 (plugin minimum remains 7.4)
- Plugin: 1.0.3
- Database: MySQL 8.0.35
- Browser: blocked by in-app browser Windows ACL initialization failure
- Site timezone: +00:00

Compatibility metadata remains unchanged and consistent with the feature plan:
WordPress 6.2+, WooCommerce 8.2+, PHP 7.4+, with HPOS compatibility already
declared. The local validation environment is newer than the declared tested-up
to metadata.

## Evidence index

- [Fixture results](fixture-results.md)
- [Security and privacy](security-privacy.md)
- [Performance](performance-results.md)
- [Browser QA](browser-qa.md)

## Static quality gates

| Command | Outcome |
|---|---|
| `php -l` on all changed PHP and both fixture files | PASS |
| `node --check admin/js/analytics-dashboard.js` | PASS |
| Targeted `vendor\bin\phpcs.bat --standard=phpcs.xml ...` | No errors in changed production files; existing warnings remain |
| `composer phpcs` | FAIL — repository baseline errors in unrelated/mixed-line-ending files, including settings views, analytics helpers, public code, and other pre-existing files |
| `git diff --check` | PASS (Git emitted only configured LF-to-CRLF conversion notices) |
| Integration fixture | PASS |
| 100-number/100,000-row benchmark | PASS for All, exact, and Unattributed |

The repository-wide PHPCS failure is not caused solely by this feature and was
not broadened into an unrelated cleanup. Changed production files report no
PHPCS errors; warnings include dynamic prepared-SQL analysis and pre-existing
nonce/filesystem recommendations.

## Contract reconciliation

The implementation matches the additive request/response contract:

- omitted or `all` keeps prior All-number behavior;
- `unattributed` scopes NULL and zero stored IDs;
- configured positive decimal IDs scope all dashboard queries;
- malformed, unknown, stale, or removed IDs return All with bounded localized
  fallback context;
- current and prior KPI periods receive the same scope;
- all five successful actions include identical `filter_context` structure;
- configured labels are escaped and phone values are masked;
- Unattributed does not expose a misleading report deep link;
- no schema, tracking, retention, settings-storage, or storefront contract was
  changed.

## Release recommendation

Do not call the feature fully release-validated yet. Automated functional,
security/privacy, preservation, syntax, and scale checks pass. Release still
requires the authenticated browser matrix in `browser-qa.md` and a project
decision on the existing repository-wide PHPCS baseline.

## Risks and limitations

- Authenticated responsive, keyboard, throttling, and visual stale-retry checks
  could not run because the in-app browser runtime failed at the sandbox layer.
- Repository-wide PHPCS remains red from existing violations outside this
  feature; changed production files have no PHPCS errors.
- Trend rows are pre-aggregated into 15-minute UTC buckets before timezone/day,
  week, or month bucketing, reducing large-range memory and runtime while
  preserving contemporary 15-minute-aligned timezone boundaries.
- Historical removed IDs remain included under All and display with the
  existing Unknown label; they are intentionally not selectable.