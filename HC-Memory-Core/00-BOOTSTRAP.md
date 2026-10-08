# HC MEMORY CORE V1.0

Status: PARTIAL — NOT ACCEPTED
Updated: 2026-10-08

## Mandatory startup lookup
1. Fetch this file from HCDecorAD/HCDecorHUB on GitHub.
2. Fetch 01-GLOBAL-RULES.md, 02-SYSTEM-ARCHITECTURE.md, 03-PROJECT-REGISTRY.json, 04-SOURCE-INDEX.json, and 05-CONNECTION-ROUTES.json.
3. Identify requested project and verify its latest commit and relevant source files.
4. Verify local build/test evidence before calling anything PASS.
5. When a connection fails, record UNVERIFIED or BLOCKED; do not invent source, success, or permissions.

## Operational rules
- Local First; primary lanes A Gateway/TransWarp and B MeshCentral/Mesh Agent.
- RDC/HCDR are rescue only, not dual-lane acceptance.
- Preserve verified PASS modules unless regression is evidenced.
- ANALYZE → BUILD/FIX → RUN → REAL TEST → FIX → RETEST → PASS.
- PASS green, ACTIVE blue, unknown UNVERIFIED.
- Automatic periodic execution is opt-in.
- Never commit secrets or personal data to public repositories.

## Memory limits
This file does not auto-execute on opening a new ChatGPT conversation. Project instructions or a connected workflow must explicitly retrieve it. GitHub commits do not verify local builds.

## Latest connectivity observation
2026-10-08: Mesh health returned ok, active local-direct; Mesh status reported gateway UP, Zeus Z06, one client, ownerLock true. No local filesystem read/write acceptance has been performed.

## Job checkpoint schema
Project, module, changed files, commit SHA, evidence paths, tests, status, next action.
