# Fixture Results

## Static and Smoke Validation

- `php -l` passed for the migrator, bootstrap, installer, and fixture runner.
- Standalone legacy and mixed-schema smoke tests passed, including false/null preservation,
  unknown container-shell retention, canonical precedence, malformed-input safety, and
  idempotence.

## WP-Backed Matrix

Pending: the local WordPress database connection is unavailable in this environment. Run the
fixture runner through WP-CLI or `wp-load.php` on a connected disposable site for R1-R3,
missing-option, malformed-option, failed-write, and high-volume cases.

## Expected Analytics Defaults

`analytics.enabled=true`, `retention_days=365`, `track_ip=true`, `dedupe_hours=24`,
`exclude_bots=true`, and `visitor_cookie_ttl=30`; existing false, null, blank, zero, empty,
and customized values must remain authoritative.