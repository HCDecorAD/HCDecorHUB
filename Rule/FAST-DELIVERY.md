# FAST-DELIVERY — AUTONOMOUS DELIVERY WITH MINIMUM MEANINGFUL GATES

Status: ACTIVE / HARD / GLOBAL
Priority: P1
Rule ID: FAST_DELIVERY
Version: 1.0
Scope: DELIVERY / CODING / FIX / BUILD / TEST

## Goal
Maximize useful delivery speed without weakening safety or real acceptance.

## Execution contract
1. GOAL OVER STEPS: Owner gives the outcome; agent decomposes and executes the reversible implementation steps.
2. CONTINUE UNTIL TERMINAL: continue Analyze -> Build/Fix -> Run -> Test -> Package/Publish until DONE or a true Owner Boundary.
3. PASS IS NOT STOP: PASS is a successful meaningful acceptance checkpoint and execution continues automatically.
4. INTERNAL CHECKS ARE NOT PASS: file-exists, syntax, lint, build, process-running, HTTP-200 and similar signals are internal CHECKs unless they are the actual user-facing acceptance.
5. ONE ACCEPTANCE PER CAPABILITY: prefer one real targeted acceptance proving the capability end-to-end. Add gates only for distinct risk/contract boundaries.
6. FIX FIRST, REPORT LATER: self-repair ordinary reversible failures; do not stop to request permission for a repair already inside the approved goal.
7. AFFECTED TEST ONLY: rerun tests/evidence affected by the change. Full regression is reserved for release/freeze, core/shared changes, or explicit contract requirements.
8. NO REDO PASS: retain valid PASS evidence until an affected dependency/acceptance contract changes.
9. EARLY USABLE RELEASE: once the core capability is genuinely usable, package/freeze a known-good version before optional enhancement.
10. MINIMUM SUFFICIENT EVIDENCE: collect enough evidence to prove the contract; do not multiply equivalent evidence.

## Terminal states
- RUNNING: work remains and a self-service path exists.
- PASS: meaningful capability acceptance succeeded; continue if scope remains.
- DONE: scoped goal is fully completed.
- BLOCKED: all authorized self-service paths are exhausted or a true Owner Boundary is reached.

## Owner Boundary
Stop only for credentials/authentication the agent cannot obtain, security/privacy approval, irreversible/destructive decision, paid commitment, ambiguous product decision that materially changes the goal, or unavailable required capability after authorized fallbacks are exhausted.

## Relationship
RuleGuard/RuleBootstrap and security/WRITE rules remain authoritative. This rule changes delivery cadence, not authorization.

## Acceptance
A normal coding/fix mission should require no Owner NEXT between reversible steps and should surface only meaningful PASS/DONE/BLOCKED milestones.
