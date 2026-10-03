# HC Sentinel — Build Phases

## Completed foundation

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

## Operator layer

🔵 P11 — Operator Layer  
Build:
- command parser
- direct user command flow
- project + viewport resolution
- READY / REVIEW_REQUIRED planning

🟢 PASS — 2/2  
✅✨ DONE

🔵 P12 — Project Profiles  
Build:
- project registry
- profiles
- viewport policy
- capability declaration

🟢 PASS — 1/1  
✅✨ DONE

🔵 P13 — Baseline Manager  
Build:
- project/viewport baseline mapping
- revision requirement
- explicit approval requirement
- fallback to default baseline

🟢 PASS — 2/2  
✅✨ DONE

🔵 P14 — Repair Bridge  
Build:
- route finding to capable worker
- healthy-worker selection
- WAITING_CAPABILITY fail-closed behavior
- repair dispatch mission

🟢 PASS — 3/3  
✅✨ DONE

Verified smoke:
`Sentinel kiểm tra GSC mobile -> READY -> GSC -> Mobile -> Approved Baseline -> DISPATCHED -> autodebug-ui`

Full regression:
`32/32 PASS — 0 FAIL`

Gate: `OPERATOR_READY_CORE`

## Next practical phases

🔵 P15 — Real Target Profiles  
Goal: bind actual HC project targets, URLs, routes, viewports and visual rules.

🔵 P16 — Command Center UI  
Goal: provide a direct operator interface for Run Now / Compare / Verify / Route Repair.

🔵 P17 — Persistent Baseline & Evidence Store  
Goal: persist approved baselines, revisions, evidence metadata and project history safely.

🔵 P18 — Live Repair Bridge  
Goal: connect repair routing to real AutoDebug / Agent Control / project workers with execution evidence.

🔵 P19 — End-to-End Project Runs  
Goal: run real project scenarios through Observe -> Detect -> Repair -> Verify -> DONE.

## Build rule

- Do not redo a phase that already has execution-evidence PASS.
- Manual/operator command keeps priority over schedule.
- GREEN/DONE requires execution evidence.
- UNCERTAIN_EFFECT must fail closed.
- One failed lane must not block unrelated project lanes.
- Runtime stays local-first; GitHub stores sanitized versionable DATA.
