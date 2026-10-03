# TransWarp v0.5 — Cross-Chat Adaptive Transport

## Purpose
Any new chat must identify TransWarp, resolve local capability, and choose the safest available execution route before touching project source.

## Canonical discovery
Read:
- `config/transwarp-local-capability.json`
- `docs/TRANSWARP-CROSS-CHAT-BOOTSTRAP.md`

Never infer local capability from the chat title. iMaster is only a chat group.

## Required handshake for every new chat
1. Resolve the capability manifest from `HCDecorAD/HCDecorHUB`.
2. Detect an authorized local bridge with filesystem + terminal access.
3. Preferred bridge capability: Remote Desktop Commander or an equivalent authorized local connector.
4. If a direct local bridge exists:
   - probe P11 ingress `/health`;
   - if ingress is healthy, submit `transwarp-local/job-v1`;
   - otherwise use direct local inbox or Local Runner one-shot;
   - read evidence from `D:\HC_DATA\queue\transwarp\evidence`;
   - GitHub is sync only.
5. If no direct local bridge exists:
   - return `TRANSWARP_LOCAL_CONNECTOR_REQUIRED`;
   - do not pretend localhost is reachable;
   - do not silently downgrade to HCDR.
6. HCDR may be used only when explicitly selected for fallback/recovery.

## Transport Policy FINAL v1\nAUTO_ROUTE\n- HCDR primary while healthy.\n- Local Bridge fallback on HCDR queue/timeout/not-claimed/rate-limit/unhealthy.\n- Never run the same job_id/correlation_id on two routes at once.\n- A running job stays on its selected route.\n- GitHub is SYNC_ONLY.\n- If both routes fail, return BLOCKED.\n\n## Route order\nHCDR_PRIMARY -> LOCAL_BRIDGE_FALLBACK\n\n## State markers
- `TRANSWARP_LOCAL_CAPABILITY_OK`
- `TRANSWARP_LOCAL_INGRESS_OK`
- `TRANSWARP_LOCAL_RUNNER_OK`
- `TRANSWARP_LOCAL_CONNECTOR_REQUIRED`
- `TRANSWARP_HCDR_FALLBACK_ACTIVE`

## Canonical prompt for a new chat
`Use TransWarp v0.5 AUTO_ROUTE. Resolve config/transwarp-local-capability.json first. Detect an authorized local bridge before running anything. Prefer P11/direct local inbox and read local evidence. Do not use HCDR unless fallback is explicitly required. If no bridge is available, report TRANSWARP_LOCAL_CONNECTOR_REQUIRED.`

## Non-negotiable rule
AUTO_ROUTE is frozen as Transport Policy FINAL v1. HCDR is primary when healthy; Local Bridge is the fallback when HCDR is unhealthy or congested. GitHub remains sync-only.
