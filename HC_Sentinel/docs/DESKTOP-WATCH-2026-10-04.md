# HC Sentinel — Desktop Watch

Date: 2026-10-04
Device: HOCUONG
Gate: `DESKTOP_WATCH_READY`

## What it does

Desktop Watch adds two monitored source types:

### ChatGPT Exact

Uses HC AutoChat registry aliases and exact conversation IDs.

Flow:

`Exact chat snapshot -> BUSY/IDLE -> fingerprint response -> STUCK -> exact command -> post-verify -> tray`

Safety:
- registered alias + exact conversation_id required
- STOP_ALL respected
- BUSY target is never sent a command
- no focus/SendKeys broadcast
- one send per stuck epoch
- cooldown before another send
- content change re-arms the watch
- Stop on DONE is opt-in

### Windows Window

Enumerates visible Windows applications and fingerprints the chosen window using a real screenshot hash.

Flow:

`Window title/process -> PrintWindow capture -> SHA256 -> ACTIVE/IDLE/STUCK -> tray`

Native Windows mode never types directly into arbitrary apps. If automatic command dispatch is desired, it must be routed to an exact registered ChatGPT alias.

## UI

Open HC Sentinel:
`http://127.0.0.1:43110`

Open the `Desktop Watch` tab.

Fields:
- Mode: ChatGPT Exact / Windows Window
- Source
- Dispatch Chat
- Interval
- Stuck after
- Cooldown
- Command
- Auto Send
- Stop on DONE

Actions per watcher:
- Start
- Stop
- Run Once
- Remove

## Recommended first rule

For a working ChatGPT project:

- Mode: `ChatGPT Exact`
- Source: registered project alias
- Dispatch Chat: same alias
- Interval: `60` seconds
- Stuck after: `300` seconds
- Cooldown: `600` seconds
- Command: `iMaster next`
- Auto Send: ON
- Stop on DONE: OFF initially

Save, then click Start.

## HOCUONG live acceptance

Native window:
- source: `HC AutoChat 24x7`
- real window capture: PASS
- transition: `ACTIVE -> STUCK`
- unsafe send: `false`

Background watcher:
- enabled through Start API
- runtime state updated automatically: PASS

ChatGPT exact:
- alias: `VISUAL`
- exact CID: verified
- localized assistant response fingerprint: PASS
- exact send through LiveTransaction: PASS
- post-verify produced a real user message ID
- watch engine transition: `ACTIVE -> SENT`

QA command:
`[Sentinel Desktop Watch QA] Watch engine auto-send verified. No action required.`

All temporary `qa-*` watchers were removed after acceptance.

## Fail-closed behavior

- exact target offline -> OFFLINE
- target busy -> BUSY
- no dispatch alias -> WAITING_DISPATCHER
- STOP_ALL -> BLOCKED
- uncertain send effect -> no blind retry

