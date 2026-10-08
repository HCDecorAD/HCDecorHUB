# HC MEMORY CORE V1.0

Status: INITIALIZED — NOT ACCEPTED
Date: 2026-10-08

## Source of truth
Read this bootstrap, project registry and evidence before making changes. Historical chat claims are not proof of PASS.

## Global rules
- Local First: build and test on HOCUONG before publishing.
- Primary lane A: Gateway → TransWarp/Local Executor → HOCUONG.
- Primary lane B: MeshCentral → Mesh Agent → HOCUONG.
- RDC/HCDR are rescue only, not dual-lane acceptance.
- Never overwrite a verified PASS module without verified regression.
- ANALYZE → BUILD/FIX → RUN → REAL TEST → FIX → RETEST → PASS.
- PASS = green, ACTIVE = blue. Unknown evidence = UNVERIFIED.
- Automatic periodic execution is opt-in, not default.
- Keep credentials and private data out of public repositories.

## Required lookup
Project registry → source index → exact Git commit → local build/test logs → checkpoint. If inaccessible, report BLOCKED or UNVERIFIED, never invent evidence.

## Job completion record
Project, module, changed files, commit SHA, evidence path, tests, result, next action.

## Initial inventory
GitHub accessible repositories: GSC, HCDecorHUB, AMONguyen, HCDecor-HCDR-Relay, HC-TransWarp-Infrastructure, HC-Design-AI-Studio, HC-English-World, HC-Creative-Factory, HC-Future.
Mesh health and status checks failed internally on 2026-10-08. Local state not verified.
