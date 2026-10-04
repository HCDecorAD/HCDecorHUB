# HC Sentinel — DAILY FINAL Real Controls

Date: 2026-10-04
Device: HOCUONG
Canonical root: `D:\HCDecorHUB\HC_Sentinel`

## Real controls

All operator controls are wired to runtime APIs:

- Run Now -> `POST /api/command`
- Compare -> `POST /api/action { action: "COMPARE" }`
- Verify -> `POST /api/action { action: "VERIFY" }`
- Route Repair -> `POST /api/action { action: "REPAIR" }`
- Add Project -> `POST /api/projects`
- Tray Notification -> Windows NotifyIcon agent + notification queue

## Compare

Compare performs a fresh Playwright capture and compares the current screenshot SHA256 with the prior evidence screenshot for the same target + viewport.

Verified on HOCUONG:
- AMO desktop Run Now -> `DONE`
- AMO desktop Compare -> `SAME`
- prior/current SHA256 matched
- failed requests: `0`

## Verify

Verify performs a new capture and quality inspection.

Verified on HOCUONG:
- GSC mobile -> `VERIFIED`
- active findings: `0`
- intentional `BROKEN_MEDIA_HINT x3` remains in suppressed findings
- failed requests: `0`

## Repair

Repair routes the latest active project finding into persisted:
`runtime-state/repair-queue.json`

Golden smoke used a temporary project/finding and produced:
- status: `ROUTED`
- real repair job id
- persisted queue item
- tray warning event

The temporary project/finding/repair job was restored/removed after the smoke run.

## Add Project

Add Project persists custom targets to:
`data/live/targets.json`

Targets are available immediately without restarting Sentinel.

Golden smoke:
- temporary project -> `ADDED`
- runtime resolved it immediately
- state restored after test

## Tray Notification

Windows tray agent:
- singleton mutex `HC_SENTINEL_TRAY`
- NotifyIcon
- double-click opens Command Center
- context menu Open / Exit
- watches `runtime-state/tray-notifications.jsonl`
- balloon notification for info/warning/error

Verified:
- tray singleton process count: `1`
- notification queue received live mission messages

## Regression

Local HOCUONG:
`102/102 PASS — 0 FAIL`

Golden smoke:
- Run Now: DONE
- Compare: SAME
- Verify: VERIFIED
- Add Project: ADDED
- Repair: ROUTED
- Tray: ACTIVE / SINGLETON
- UI real controls: TRUE

Gate: `DAILY_FINAL_READY`
