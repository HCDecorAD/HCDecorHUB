# RULE-BOOTSTRAP — AUTO-LOAD RULES BEFORE EXECUTION

Status: ACTIVE / HARD / GLOBAL
Priority: P0
Rule ID: RULE_BOOTSTRAP
Version: 1.1
Scope: ALL CHATS / WORKERS / AGENTS / EXECUTION LANES

## Purpose
Rules in GitHub are executable operating policy, not passive documentation. Every supported controller/chat/worker/agent entrypoint MUST automatically load and apply the current ACTIVE Rule set before project/system execution.

## Automatic RuleGate bootstrap
On NEW CHAT / NEW WORKER / NEW AGENT / NEW EXECUTION LANE:
1. AUTO-RESOLVE source-of-truth: HCDecorAD/HCDecorHUB@main/Rule/RULES-MANIFEST.json.
2. AUTO-FETCH the manifest. Do not wait for the Owner to say "load Rule".
3. AUTO-LOAD every ACTIVE rule applicable to the requested scope.
4. AUTO-APPLY RULE_GUARD conflict, priority, supersedes and override resolution.
5. Build an in-session RuleContext containing at minimum: source, manifest hash/version evidence, loaded rule IDs/versions, resolved priority order, and RuleGate status.
6. Only after RuleGate = PASS may execution/mutation proceed.
7. The first controller that spawns workers MUST require each spawned worker to run the same RuleGate; inheritance by assumption is forbidden.

## Runtime refresh
Re-run RuleGate automatically when:
- a new chat/worker/agent starts;
- execution switches lane/transport;
- the manifest SHA/version changes;
- the current session has no verifiable RuleContext;
- a requested action enters a scope not covered by the already-loaded RuleContext.

If the manifest SHA/version is unchanged, an already verified in-session RuleContext may be reused to avoid unnecessary repeated downloads.

## Fail closed for mutation
If the manifest/rules cannot be loaded, RuleContext cannot be verified, or RULE_GUARD reports an unresolved conflict:
- READ/status/diagnostic work may continue only when safe and authorized.
- WRITE/MUTATION is BLOCKED.
- Never silently fall back to conversational memory, an old cached rule copy, or guessed policy.

## Source-of-truth and local mirror
- Canonical authority: GitHub main Rule directory and RULES-MANIFEST.json.
- A HOCUONG local Rule mirror may accelerate startup, but it is a cache/mirror only.
- Before trusting a local mirror for mutation, verify it against canonical manifest/version evidence when network access is available.
- Updating the HOCUONG mirror is a HOCUONG WRITE and therefore MUST obey WRITE_SAFETY_RDC.

## HOCUONG mutation
WRITE_SAFETY_RDC remains mandatory:
RULEGATE PASS -> RDC WRITE -> READ-BACK -> REAL TARGETED TEST -> PASS -> SYNC -> DONE.

Mesh/HCDR may inspect, orchestrate, health-check and verify, but they do not replace RDC for HOCUONG WRITE.

## Evidence
A compliant lane should expose compact evidence when supported:
RULEGATE PASS | manifest=<sha/version> | active=<count> | scope=<scope>

Do not claim Rule compliance, PASS, or DONE without RuleGate evidence.

## Acceptance
PASS requires a real bootstrap test proving a fresh supported execution entrypoint loads the current manifest automatically without the Owner manually requesting Rule loading.
DONE requires the auto-load mechanism to be published/synced to the intended runtime entrypoints and the targeted acceptance test to pass.
