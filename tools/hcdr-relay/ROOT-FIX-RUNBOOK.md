# HCDR Root Fix Runbook

## Root causes confirmed
- Production worker: hcdr-job + hcdr-relay/v1 only.
- V1.2 launcher references missing relay-agent-v12.mjs.
- Startup-folder lifecycle has no authoritative supervisor/recovery.
- Runtime launcher performs git pull and is not release-pinned.
- Heartbeat alone does not restart a dead worker.
- AutoChat public evidence previously could be assembled from different runs.

## Canonical recovery path
1. Run ROOT-FIX-ALL.bat.
2. Diagnose validates node/git/gh auth, agent files, heartbeat and Node process.
3. Production repair restarts the known v1 worker and requires fresh heartbeat.
4. V1.2 stays isolated/fail-closed until relay-agent-v12.mjs exists and passes static/runtime tests.
5. Once production HCDR is reachable, implement/test the v1.2 worker through HCDR.
6. Replace Startup-folder launch with a supervised Windows service and pinned release.
7. Run AutoChat A1-A7 under one acceptance run_id.
8. Generate PUBLIC-ACCEPTANCE.json only from same-run evidence.
9. Promote Public only when strict gate passes.

## Rule
Do not create more health issues while the worker is offline. Do not weaken workspace guard. Do not auto-retry uncertain side effects.
