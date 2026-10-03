# HC Sentinel — Build Phases

## Completed milestones

🔵 P1-P10 — Foundation  
🟢 PASS  
✅✨ DONE

🔵 P11-P14 — Operator Core  
🟢 PASS  
✅✨ DONE

🔵 P15-P19 — Practical Operator Runtime  
🟢 PASS  
✅✨ DONE

🔵 P20-P24 — Operations Hardening  
🟢 PASS  
✅✨ DONE

🔵 P25-P29 — Daily Operations Runtime  
🟢 PASS  
✅✨ DONE

Gate: `LOCAL_OPERATOR_READY`

🔵 P30 — Local Bootstrap  
- validate Node runtime
- create runtime-state
- create evidence/live
- create data/live
- create logs

🟢 PASS — 1/1  
✅✨ DONE

🔵 P31 — Live App Binding  
- local server serves UI
- UI calls status API
- UI calls command API
- safe static-path handling

🟢 PASS — 1/1  
✅✨ DONE

🔵 P32 — Evidence Index  
- persist evidence metadata
- filter by project
- filter by type

🟢 PASS — 1/1  
✅✨ DONE

🔵 P33 — Settings Store  
- safe defaults
- allowlisted settings only
- persisted theme/policy values

🟢 PASS — 1/1  
✅✨ DONE

🔵 P34 — Golden App Acceptance  
Validates:
- UI served
- status = SENTINEL_READY
- command = RUNNING
- evidence API returns indexed data
- settings theme switches to light

🟢 PASS — Acceptance Flow  
✅✨ DONE

Acceptance:
`ui=true -> status=SENTINEL_READY -> command=RUNNING -> evidence=1 -> theme=light`

Full regression:
`63/63 PASS — 0 FAIL`

Gate: `APP_READY`

## Build rule

- Do not redo execution-evidence PASS phases.
- Manual/operator command outranks schedule/watch.
- GREEN/DONE requires evidence.
- Baselines require explicit review before promotion.
- Medium findings route but do not auto-repair by default.
- Critical unresolved findings block release.
- Runtime remains local-first.
