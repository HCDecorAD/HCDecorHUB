# HC Mobile Runtime Execution Plan

Status vocabulary: TODO -> BUILD -> CI_PASS -> DEVICE_PASS -> RUNTIME_PASS.

1. R1 Transport choice: Android outbound client (no inbound port required), HTTPS/WebSocket compatible, authenticated pairing.
2. R2 Secure identity: generate device ID + random secret; persist secret with Android Keystore-backed storage; never commit secret.
3. R3 Runtime service: foreground/background-safe worker receives jobs, validates schema, enforces idempotency, executes allowlisted commands.
4. R4 Command API: device.info and media.list read-only first; structured signed result.
5. R5 Safe mutation: trash requires preview/confirm/verify; restore verifies recovery; permanent delete disabled by default.
6. R6 Queue/reconnect: durable queue and jobId deduplication across process/network restart.
7. R7 Controller bridge: iMaster/Gateway endpoint can submit command and read result.
8. R8 Local-first routing: local direct when reachable; secure gateway then Mesh fallback.
9. R9 Real runtime E2E: controller -> transport -> Samsung -> result, then trash -> verify -> restore.
10. R10 Production hardening: TLS, token rotation/revoke, rate limit, audit export, release signing/update path.

Current check 2026-10-07:
- Android P1-P10 device acceptance: PASS.
- Stable dev signing install-over: PASS.
- iMaster Mesh callable path from this session: FAIL/BLOCKED (MCP SSE probe 404).
- Reachable Android authenticated runtime transport: NOT IMPLEMENTED.
- Therefore RUNTIME_PASS is blocked until R2-R7 are built and an external controller lane is reachable.
