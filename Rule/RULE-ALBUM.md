# HCDecor RULE ALBUM — OPERATING POLICY MAP

This is the human-readable map. Runtime authority remains RULES-MANIFEST.json.

## 00 — BOOT / GOVERNANCE
- RULE_GUARD [P0] — resolve conflicts and priority.
- RULE_BOOTSTRAP [P0] — auto RuleGate on every new Chat/Worker/Agent/Lane; load only applicable ACTIVE rules.

## 10 — SPEED / DELIVERY
- FAST_DELIVERY [P1] — Goal -> autonomous execution -> minimum meaningful gates -> DONE.
- TEST_DONE [P2] — real capability acceptance and usable release contract.

## 20 — WRITE / TRANSPORT
- WRITE_SAFETY_RDC [P1] — HOCUONG mutation uses RDC, then read-back + real targeted acceptance.
- NO_COMPLEX_INLINE_POWERSHELL [P1] — complex shell logic goes to script files.
- CONTINUE_AVAILABLE_TRANSPORT [P1] — do not stop just because one authorized read/control lane is unavailable.
- LOCAL_FIRST [P3] — HOCUONG is local execution authority where applicable.
- DUAL_LANE [P4] — read/status/verify lane separation; WRITE routing is overridden by WRITE_SAFETY_RDC.

## 30 — ORCHESTRATION
- ORCHESTRATION [P5] — controller/worker coordination.

## 40 — UI / REPORTING
- UI_STATUS_COLOR [P2] — PASS = blue; DONE = green.

## Canonical mission flow
NEW CHAT/WORKER
  -> RuleGate
  -> load CORE + scoped ACTIVE rules
  -> resolve conflicts
  -> GOAL
  -> autonomous Build/Fix/Test loop
  -> internal CHECKs stay internal
  -> real targeted acceptance = PASS
  -> continue automatically while scope remains
  -> Package/Public/Freeze as required
  -> DONE

## Reporting contract
Default progress reporting is compact:
RUNNING | current capability | blocker only if real
PASS    | meaningful acceptance evidence
DONE    | final usable outcome
BLOCKED | exact Owner Boundary + exhausted paths

Do not turn implementation details into Owner checkpoints.

## Rule hygiene
- Add a permanent rule only for a recurring/systemic failure mode or stable operating contract.
- Prefer updating/merging an existing rule over adding overlapping rules.
- Archive superseded rules.
- Keep P0 core minimal; load scoped rules on demand.
- Periodically compact the album/manifest to prevent context bloat.
