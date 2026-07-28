# Dashboard Number Filter Browser QA

Status: BLOCKED — the in-app browser runtime failed to initialize twice because
the Windows sandbox ACL helper returned `helper_unknown_error: apply deny-read
ACLs`.

| Scenario | Result | Evidence |
|---|---|---|
| Fresh load defaults to All | PASS (server-rendered markup fixture) | Native select marks All selected |
| Zero, one, and two configured numbers | Pending | — |
| Keyboard and two-interaction Apply flow | Pending | — |
| 360px and 200% zoom | Pending | — |
| Date preset and comparison retention | Pending | — |
| Stale selection one-warning/one-retry | Pending | — |
| Throttled 101 → 202 last-request-wins | Pending | — |
| Assets absent from unrelated admin pages | Static code review PASS; browser confirmation blocked | Asset enqueue remains restricted to dashboard/reports screens |

No screenshots were produced. Authenticated interaction, viewport, zoom,
keyboard, throttling, and stale-batch visual checks remain required when the
in-app browser connection is available.