# HC HCDR AUTORECOVERY — HARD WORKFLOW SKILL v1.0

## Trigger
Activate whenever an HCDecorHUB/HOCUONG GitHub Relay job remains open without a result, BAT execution is blocked, a worker appears stalled, or the user requests CHECK > BAT > GITHUB > RUN > FIX > PUBLIC.

## Mandatory execution
1. **CHECK** current GitHub job state, comments, correlation IDs, local heartbeat evidence and existing PASS checkpoints. Never infer PASS from service/task Running alone.
2. **ANALYZE** exact bottleneck: API auth, relay polling, local claim/state, execution allowlist, build/test, or production deploy gate. Inspect existing supervisor/watchdog/agent before writing code.
3. **REUSE FIRST**: existing BAT, scripts, checkpoints and 4-worker relay pool. No duplicate issues, repeated manual screenshot checks or unnecessary new workers.
4. **BAT**: package a single idempotent, bounded diagnostic/recovery/test BAT where missing; logs and exit codes required. Keep secrets out of logs.
5. **GITHUB**: commit BAT with traceable SHA; submit an allowlisted HCDR job with source_id, mission_id, correlation_id and explicit approval scope.
6. **CHECK / RUN**: verify HOCUONG roundtrip by actual issue result comment, local exit code and evidence. GitHub API read PASS is not worker claim PASS; heartbeat PASS is not roundtrip PASS.
7. **PARALLEL**: use up to 4 independent workers for safe, non-overlapping tasks (build, lint, unit/E2E, source inspection). Respect mutation lane locks; never run conflicting writes concurrently.
8. **FIX → RETEST** only failing modules; preserve passed modules, no reset/delete of production. Consult an authorized expert/Quân sư if accessible; do not claim consultation if blocked.
9. **CLEAN QUEUE**: close obsolete/no-result jobs as not_planned only after checking job state, dependencies and active local claims. Do not delete issue history, quarantine or rerun uncertain mutating jobs automatically.
10. **PUBLIC GATE**: verify real Publish Engine target, auth, preview, rollback, smoke/E2E, build/lint and deployment approval. localStorage publish is NOT real Public. A BLOCKED deploy gate stays BLOCKED until explicit owner authorization and gate evidence; never bypass allowlists or owner lock.
11. **REPORT** compact PASS/BLOCKED/UNKNOWN with evidence links and one next action. Ask owner for one action only when all authorized remote paths fail. No repeated requests for steps already completed.

## Safety
Local First; no Vercel/Supabase; no auto-start Zeus 24/7; no production deploy, service restart, policy widening, secrets, destructive deletes, or publication without explicit approval. User instruction to pursue Public does not waive deployment gate or independent confirmation. No fabricated test results. End each cycle when blocked, documenting the exact gate rather than promising unattended background execution.

## Project-specific known paths
- Repo: HCDecorAD/HCDecorHUB
- Relay repo: HCDecorAD/HCDecor-HCDR-Relay
- Local repo: D:\HCDecorHUB\repos\HCDecorHUB
- Relay scripts: tools/hcdr-relay/user-supervisor.ps1, watchdog.ps1, relay-agent.mjs, HCDR-Remote-Free.cmd
- Heartbeat: D:\HCDecorHUB\runtime\hcdr-relay-heartbeat.json
- State: D:\HCDecorHUB\runtime\hcdr-relay-state-v2.json
- Visual Builder: D:\HCDecorHUB\HC_Visual_Builder
