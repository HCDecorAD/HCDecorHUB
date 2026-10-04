# HC Sentinel — iMaster Desktop Watch Production

Date: 2026-10-04

## Runtime
- Local API: http://127.0.0.1:43110
- Status: SENTINEL_READY
- Sentinel version: 1.2.0

## Production watches
1. prod-master
   - Source: CHATGPT exact alias MASTER
   - Interval: 60s
   - Stuck threshold: 300s
   - Cooldown: 600s
   - Auto-send: enabled
   - Command: iMaster next
   - Initial live state: BUSY

2. prod-visual
   - Source: CHATGPT exact alias VISUAL
   - Interval: 60s
   - Stuck threshold: 300s
   - Cooldown: 600s
   - Auto-send: enabled
   - Command: iMaster next
   - Initial live state: ACTIVE

3. prod-autochat-window
   - Source: native Windows window HC AutoChat 24x7
   - Interval: 60s
   - Stuck threshold: 180s
   - Auto-send: disabled (fail-closed)
   - Initial live state: ACTIVE

## Safety
- Auto command dispatch is permitted only for exact registered ChatGPT aliases.
- Native Windows window watches are alert-only.
- Busy exact chats do not receive a command.
- Cooldown and sentEpoch prevent duplicate dispatch.
- STOP_ALL flag remains authoritative.
- stopOnDone remains disabled for production because generic DONE text may appear inside normal work output.

## Fix included
- AutoChat bridge stdout/stderr forced to UTF-8 to prevent Vietnamese Windows charmap failures.
- Commit: 3f80d00

## Verification
- Exact live snapshot: VISUAL IDLE/online, MASTER BUSY/online.
- Desktop Watch targeted tests: PASS.
- Full HC Sentinel regression: 109/109 PASS.
- Live watch configuration persisted in runtime-state/desktop-watch.json.

Status: PRODUCTION_ARMED
