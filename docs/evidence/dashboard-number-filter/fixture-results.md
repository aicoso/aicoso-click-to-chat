# Dashboard Number Filter Fixture Results

Status: PASS — 2026-07-28.

## Command

```powershell
php -d mysqli.default_port=10009 -d display_errors=1 -r "require 'C:/Users/SAYAN/Local Sites/aicoso2/app/public/wp-load.php'; require 'C:/Users/SAYAN/Local Sites/aicoso2/app/public/wp-content/plugins/aicoso-click-to-chat/tests/integration/dashboard-number-filter-fixtures.php';"
```

## Expected matrix

| Scope | Current clicks | Prior clicks |
|---|---:|---:|
| All | 8 | 6 |
| Number 101 | 3 | 2 |
| Number 202 | 2 | 1 |
| Unattributed | 2 | 2 |

## Result

`dashboard-number-filter-fixtures: PASS`

The matrix passed for all six query methods and all five dashboard AJAX
actions. It also covered an omitted filter, an empty future range, malformed
and removed tokens, a concurrently removed selected number, comparison
disabled, rename/default-number changes, and exact restoration of settings and
non-fixture analytics rows.

The pre-implementation red run was not retained because implementation resumed
from an already-modified working tree. The final fixture contains the intended
regression assertions and passes against the local WordPress database.