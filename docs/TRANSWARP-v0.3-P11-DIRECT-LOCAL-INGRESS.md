# TransWarp v0.4.1 — P11 Direct Local Ingress

P11 provides a local manifest ingress in front of the existing Local Runner.

Flow:
Authorized Chat Bridge -> Direct Local Ingress -> D:\HC_DATA\queue\transwarp\inbox -> Local Runner -> evidence -> GitHub sync.

Rules:
- P1-P10 remain unchanged and PASS.
- Ingress accepts only `transwarp-local/job-v1`.
- Explicit `approved=true` is required.
- Allowed actions: `process`, `powershell_file`.
- `cwd` must stay under `D:\HCDecorHUB` or `D:\HC_DATA`.
- Local token is stored at `D:\HC_DATA\queue\transwarp\ingress.secret`.
- Default bind is `127.0.0.1:8787`.
- HCDR remains `FALLBACK_ONLY`.
- GitHub remains `SYNC_ONLY`.

Cross-chat requirement:
A normal chat cannot reach HOCUONG localhost by itself. The session needs an authorized local bridge with terminal/filesystem capability. Once that bridge is connected, the chat should probe `/health`, submit a manifest, and verify local evidence before declaring DONE.

If no bridge is available:
`TRANSWARP_LOCAL_CONNECTOR_REQUIRED`

Never silently fall back to HCDR.
