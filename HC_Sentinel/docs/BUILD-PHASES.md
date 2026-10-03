# HC Sentinel — Build Phases

## Foundation

🔵 P1 — Sentinel Kernel  
🟢 PASS  
✅✨ DONE

🔵 P2 — Visual Observer  
🟢 PASS  
✅✨ DONE

🔵 P3 — Visual Diff Engine  
🟢 PASS  
✅✨ DONE

🔵 P4 — Quality Sensors  
🟢 PASS  
✅✨ DONE

🔵 P5 — Decision & Repair Routing  
🟢 PASS  
✅✨ DONE

🔵 P6 — Verify-to-DONE Loop  
🟢 PASS  
✅✨ DONE

🔵 P7 — Watch / Event / Schedule Autonomy  
🟢 PASS  
✅✨ DONE

🔵 P8 — Replay & Observability Foundation  
🟢 PASS  
✅✨ DONE

🔵 P9 — Multi-Project Sentinel  
🟢 PASS  
✅✨ DONE

🔵 P10 — Golden Run  
🟢 PASS  
✅✨ DONE

Gate: `SENTINEL_READY`

## Operator core

🔵 P11 — Operator Layer  
🟢 PASS — 2/2  
✅✨ DONE

🔵 P12 — Project Profiles  
🟢 PASS — 1/1  
✅✨ DONE

🔵 P13 — Baseline Manager  
🟢 PASS — 2/2  
✅✨ DONE

🔵 P14 — Repair Bridge  
🟢 PASS — 3/3  
✅✨ DONE

Gate: `OPERATOR_READY_CORE`

## Practical operator runtime

🔵 P15 — Real Target Profiles  
Build:
- GSC public target
- AMO public target
- desktop/mobile viewports
- target registry

🟢 PASS — 2/2  
✅✨ DONE

🔵 P16 — Command Center UI  
Build:
- real static app UI
- command input
- Run Now / Compare / Verify / Route Repair
- project cards
- mission runtime
- workers/evidence/activity
- dark theme
- light gray-white theme
- persisted theme toggle

🟢 PASS — 2/2 + browser smoke  
✅✨ DONE

Browser smoke:
`dark -> light -> command -> RUNNING`

🔵 P17 — Persistent Baseline & Evidence Store  
Build:
- atomic JSON store
- read/write/update
- temp-file replace to reduce partial-write risk

🟢 PASS — 1/1  
✅✨ DONE

🔵 P18 — Live Repair Bridge  
Build:
- HCDR repair envelope
- execution-evidence contract
- fail-closed wording
- real GitHub/HCDR dispatch transport

🟢 PASS — 1/1 + live dispatch  
✅✨ DONE

Live dispatch:
`HCDecorAD/HCDecor-HCDR-Relay#1265`

🔵 P19 — End-to-End Project Runs  
Build:
- Playwright capture against real GSC + AMO public targets
- screenshot evidence
- network/console capture
- quality finding extraction
- evidence artifact

🟢 PASS — 1/1 + real public target E2E  
✅✨ DONE

Results:
- GSC: reachable, failed requests 0; 3 medium `BROKEN_MEDIA_HINT` findings routed to P18.
- AMO: reachable, failed requests 0; quality findings 0.

Full regression:
`39/39 PASS — 0 FAIL`

Gate: `OPERATOR_READY`

## Build rule

- Do not redo a phase that already has execution-evidence PASS.
- Manual/operator command keeps priority over schedule.
- GREEN/DONE requires execution evidence.
- UNCERTAIN_EFFECT must fail closed.
- One failed lane must not block unrelated project lanes.
- Runtime stays local-first; GitHub stores sanitized versionable DATA.
- A target defect does not equal a Sentinel phase failure when Sentinel detects, records, and routes it correctly.
