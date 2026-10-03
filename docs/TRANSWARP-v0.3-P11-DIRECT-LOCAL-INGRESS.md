# TransWarp v0.3 — P11 Direct Local Ingress

P11 adds a local manifest ingress in front of the existing Local Runner.

Flow:
Chat/Desktop/connector -> Direct Local Ingress -> D:\HC_DATA\queue\transwarp\inbox -> Local Runner -> evidence -> GitHub sync.

Rules:
- P1-P10 remain unchanged and PASS.
- Ingress accepts only transwarp-local/job-v1.
- Explicit approved=true is required.
- Allowed actions: process, powershell_file.
- cwd must stay under D:\HCDecorHUB or D:\HC_DATA.
- Local token is generated at D:\HC_DATA\queue\transwarp\ingress.secret.
- Default bind is 127.0.0.1:8787.
- HCDR remains FALLBACK_ONLY.
- GitHub remains SYNC_ONLY.

Important:
A ChatGPT session still needs an authorized desktop/plugin/connector route to reach the local ingress. P11 removes HCDR from the execution path once such a route is present; it does not bypass product security or network boundaries.
