# Dashboard Number Filter Performance Results

Status: PASS — 2026-07-28.

## Dataset

- Configured numbers: 100
- Retained click rows: 100,000

## Query plans

Representative exact-number trend query:

- Selected key: `number_clicked_at`
- Estimated rows: 1,000
- Temporary rows were removed after the run: confirmed 0 remaining.

## Warmed refreshes

Target: at least 19 of 20 complete five-widget refreshes within two seconds.

Result: PASS — 20/20 warmed refreshes completed within two seconds for every
required scope.

| Scope | Under 2 seconds | P95 |
|---|---:|---:|
| All | 20/20 | 1.252928 seconds |
| Exact number | 20/20 | 0.007169 seconds |
| Unattributed | 20/20 | 0.057086 seconds |

Command:

```powershell
php -d mysqli.default_port=10009 -d display_errors=1 -r "require 'C:/Users/SAYAN/Local Sites/aicoso2/app/public/wp-load.php'; require 'C:/Users/SAYAN/Local Sites/aicoso2/app/public/wp-content/plugins/aicoso-click-to-chat/tests/integration/dashboard-number-filter-performance.php';"
```

The benchmark runs one untimed warm-up followed by 20 complete service
refreshes per scope (KPIs including comparison, trend, funnel, top products,
and top numbers). It snapshots/restores number settings and deletes only rows
bearing its unique fixture visitor prefix.