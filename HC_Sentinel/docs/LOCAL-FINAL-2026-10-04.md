# HC Sentinel — LOCAL FINAL Evidence

Date: 2026-10-04
Device: HOCUONG
Canonical local root: `D:\HCDecorHUB\HC_Sentinel`

## Source

- GitHub source: `HCDecorAD/HCDecorHUB`
- Base main at local sync: `826b9617e2170e75635c737c77c0566c0f64ae16`
- Local package version: `1.2.0`
- Prior `D:\HCDecorHUB\HC Sentinel` folder was left untouched.

## Local install evidence

- Node: `v24.21.0`
- Git: `2.55.0.windows.5`
- `runtime/install-local.ps1`: PASS
- Startup shortcut created:
  `C:\Users\DELL\AppData\Roaming\Microsoft\Windows\Start Menu\Programs\Startup\HC Sentinel.lnk`
- Desktop shortcut created.
- Watchdog scheduled task registered:
  `\HC Sentinel Watchdog`
- Task cadence: every 2 minutes.
- Runtime endpoint:
  `http://127.0.0.1:43110`

## Windows compatibility fix

The first real local run exposed a Windows ScheduledTasks compatibility issue:
`RepetitionInterval` was not settable on the trigger object on HOCUONG.

Fix:
- replaced mutable ScheduledTask trigger repetition properties with `schtasks.exe /SC MINUTE /MO 2`
- preserved hidden PowerShell execution through `watchdog-loop.ps1`

## Runtime integration fix

The previous local app controller returned a stub `RUNNING` result.

LOCAL FINAL changed runtime to:
`Command -> Parse -> Resolve Target -> Playwright Capture -> Inspect -> Evidence -> Finding Store -> DONE / ROUTED`

The API version now reads from `package.json` instead of a hard-coded value.

## Real target Golden Run

AMO mobile:
- status: `DONE`
- target: `amo-public`
- failed requests: `0`
- console messages: `0`
- evidence:
  `amo-public-mobile-2026-10-04T00-25-58-160Z`

GSC mobile:
- status: `ROUTED`
- target: `gsc-public`
- failed requests: `0`
- console messages: `1`
- finding:
  `BROKEN_MEDIA_HINT`
- severity: `medium`
- count: `3`
- evidence:
  `gsc-public-mobile-2026-10-04T00-26-02-771Z`

## Recovery evidence

Watchdog recovery simulation:
- stopped live Sentinel process
- manually triggered scheduled task
- runtime recovered to `SENTINEL_READY v1.2.0`

Startup-link recovery simulation:
- stopped live Sentinel process
- invoked the actual Startup shortcut
- runtime recovered to `SENTINEL_READY v1.2.0`

A physical Windows reboot was intentionally not forced during the active remote session.

## Local regression

`99/99 PASS — 0 FAIL`

Includes:
- prior 97 regression tests
- live controller clean-target test
- live controller routed-finding test

## LOCAL FINAL status

- Source sync: PASS
- Install: PASS
- Startup registration: PASS
- Desktop shortcut: PASS
- Watchdog scheduled task: PASS
- Hidden launch: PASS
- Local API: PASS
- Runtime version: PASS
- Real AMO mission: PASS / DONE
- Real GSC detection: PASS / ROUTED
- Local recovery: PASS
- Full local regression: PASS

Gate: `LOCAL_FINAL_READY`

Remaining external finding:
- GSC HCDR issue #1265 remains OPEN / ROUTED / TRACKED.
