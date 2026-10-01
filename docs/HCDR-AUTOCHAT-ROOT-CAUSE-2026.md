# HCDR + AutoChat Root Cause Architecture

## Root causes
1. Relay lifecycle is a foreground Node process launched from Startup/CMD, not a supervised Windows service.
2. Contract drift: production agent consumes label hcdr-job with schema hcdr-relay/v1; isolated validation jobs use hcdr-v12-test with schema v1.2.
3. Launcher runs git pull on whichever branch is checked out, so runtime code is not pinned to a release revision.
4. Heartbeat is observational only; nothing authoritative restarts a dead process.
5. AutoChat duplicates browser actionability logic over raw CDP. Dynamic ChatGPT DOM therefore creates repeated selector/focus/readback edge cases.
6. Side-effecting Send requires at-most-once semantics. Automatic retry after uncertain submit is forbidden.
7. Public gate historically checked file presence/schema inconsistently rather than one signed/immutable acceptance manifest.

## Replacement architecture
- One HCDR Supervisor installed as a Windows Service.
- Supervisor owns two explicit workers: production v1 and isolated v1.2 migration lane.
- Worker binaries/scripts pinned to a commit/release; no implicit git pull in runtime launcher.
- Each lane has explicit schema, label, source allowlist, heartbeat and state file.
- Service recovery: restart on failure with bounded backoff; health = process + fresh heartbeat + GitHub round-trip canary.
- Durable job state: RECEIVED -> CLAIMED -> EXECUTING -> RESULT_PENDING -> COMPLETED/REVIEW.
- Mutating/side-effecting operations use at-most-once/no-auto-retry after EXECUTING.
- AutoChat browser adapter should migrate from raw DOM/CDP selectors to Playwright locator/actionability primitives while retaining exact conversation-id authorization.
- One acceptance manifest is generated only after A1-A7 evidence from the same run_id. Public promotion consumes only that manifest.

## Immediate rule
Do not add more feature BATs until supervisor, contract unification, run_id evidence and at-most-once send are complete.
