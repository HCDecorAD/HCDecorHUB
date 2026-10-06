# RULE-BOOTSTRAP — LOAD RULES BEFORE EXECUTION

Status: ACTIVE / HARD / GLOBAL
Priority: P0
Rule ID: RULE_BOOTSTRAP
Version: 1.0
Scope: ALL CHATS / WORKERS / AGENTS / EXECUTION LANES

## Mandatory bootstrap
Before any system/project execution, a new chat/worker/agent MUST:
1. Resolve source-of-truth: HCDecorAD/HCDecorHUB@main/Rule/RULES-MANIFEST.json.
2. Load every manifest entry with status ACTIVE that applies to the requested scope.
3. Apply RuleGuard conflict/priority/override resolution.
4. Record/return the loaded manifest version or commit/evidence when the execution lane supports it.
5. Only then execute.

## Fail closed for mutation
If ACTIVE rules cannot be loaded or RuleGuard reports an unresolved conflict:
- READ/status work may continue only when safe.
- WRITE/MUTATION is BLOCKED until RuleGate passes.

## Session behavior
- Do not rely on conversational memory as the source of truth.
- Re-run RuleGate when starting a new chat/worker, when switching execution lane, or when the manifest changes.
- A worker spawned by another controller inherits no exemption; it must pass RuleGate itself.

## HOCUONG
For HOCUONG mutation, WRITE_SAFETY_RDC remains mandatory:
RULEGATE PASS -> RDC WRITE -> READ-BACK -> REAL TARGETED TEST -> PASS -> SYNC -> DONE.

## Acceptance
No execution lane may claim compliant execution without RuleGate PASS evidence.
