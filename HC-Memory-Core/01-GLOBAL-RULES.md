# HC Global Rules

1. Local First: build and test on HOCUONG, publish only after real verification.
2. Lane A Gateway/TransWarp; Lane B MeshCentral/Mesh Agent. Rescue RDC/HCDR only on explicit necessity.
3. Preserve verified PASS modules unless regression is evidenced.
4. ANALYZE → BUILD/FIX → RUN → REAL TEST → FIX → RETEST → PASS.
5. PASS green; ACTIVE blue; missing evidence UNVERIFIED.
6. Never infer file existence, deployment success or live route status from conversation history.
7. No default recurring automation; user must opt in.
8. Never publish credentials, personal images or sensitive logs to public repositories.
9. Check rule conflicts before adding or replacing rules.
10. Save each job checkpoint with project, module, changed paths, commit, evidence, test outcome and next action.
