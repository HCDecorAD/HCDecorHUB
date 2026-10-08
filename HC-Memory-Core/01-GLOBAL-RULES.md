# HC Global Rules

1. **LOCKED: LANE A — INTERNAL PRODUCTION ACTIVE.** Lane A is the default primary production route: Gateway → TransWarp/Local Executor → HOCUONG. Do not silently disable, demote, or reroute it.
2. Lane B MeshCentral → Mesh Agent → HOCUONG remains a separate secondary/parallel route, subject to independent verification. RDC/HCDR are rescue only.
3. Local First: build and test on HOCUONG, publish only after real verification.
4. Preserve verified PASS modules unless regression is evidenced.
5. ANALYZE → BUILD/FIX → RUN → REAL TEST → FIX → RETEST → PASS.
6. PASS green; ACTIVE blue; missing evidence UNVERIFIED. **Configured ACTIVE is not proof of completed local jobs.**
7. Never infer file existence, deployment success or live route status from conversation history.
8. No default recurring automation; user must opt in.
9. Never publish credentials, personal images or sensitive logs to public repositories.
10. Check rule conflicts before adding or replacing rules.
11. Save each job checkpoint with project, module, changed paths, commit, evidence, test outcome and next action.
12. Changes to the Lane A production designation require explicit user authorization.
