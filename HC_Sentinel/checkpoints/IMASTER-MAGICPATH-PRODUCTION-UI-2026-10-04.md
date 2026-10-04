# HC Sentinel Production UI — MagicPath Integration

Date: 2026-10-04

## MagicPath
- Project: HC Sentinel Production UI
- Project ID: 457328330811805696
- Component: HC Sentinel Command Center
- Component ID: 457328366404661248
- Revision ID: 457328366404661249

## UX
- Desktop Watch is the primary workspace.
- Light default uses gray-white surfaces; Dark mode retained.
- Watch list → selected watch detail → Run/Start/Stop/Auto/Remove.
- Add Watch supports ChatGPT Exact and Windows Window sources.
- Presets: iMaster Continue, Strict, Watch Only.
- Project Tools contains Run Check, Compare, Verify, Route Repair.
- Runtime Log is collapsed by default.
- Quick Actions: Add Project, Open Evidence, Tray Test, Backup State.
- Legacy P15/P19 and evidence/watch hooks remain hidden for compatibility tests.

## Real backend wiring
- /api/watchers and watcher start/stop/tick/remove
- /api/windows
- /api/chat-targets
- /api/projects
- /api/evidence
- /api/logs
- /api/command
- /api/action
- /api/settings
- /api/tray-test
- /api/backup-state

## Verification
- JS syntax check PASS.
- Full regression: 114/114 PASS.
- Live runtime: SENTINEL_READY v1.2.0.
- Live UI HTML markers PASS.
- app.js HTTP 200.
- styles.css HTTP 200.
- Tray Test: DELIVERED.
- Backup State: BACKED_UP.
- Existing production watchers survived restart.

Status: SENTINEL_UI_PRODUCTION_READY
