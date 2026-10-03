# TransWarp v0.4.1 — Cross-Chat Local Capability

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

## Route order
AUTHORIZED_LOCAL_BRIDGE -> DIRECT_LOCAL_INGRESS -> LOCAL_INBOX / LOCAL_RUNNER_ONESHOT -> HCDR_FALLBACK

## State markers
- `TRANSWARP_LOCAL_CAPABILITY_OK`
- `TRANSWARP_LOCAL_INGRESS_OK`
- `TRANSWARP_LOCAL_RUNNER_OK`
- `TRANSWARP_LOCAL_CONNECTOR_REQUIRED`
- `TRANSWARP_HCDR_FALLBACK_ACTIVE`

## Canonical prompt for a new chat
`Use TransWarp v0.4.1 Local-First. Resolve config/transwarp-local-capability.json first. Detect an authorized local bridge before running anything. Prefer P11/direct local inbox and read local evidence. Do not use HCDR unless fallback is explicitly required. If no bridge is available, report TRANSWARP_LOCAL_CONNECTOR_REQUIRED.`

## Non-negotiable rule
Local-first means both compute and transport should be local whenever an authorized bridge is available. HCDR and GitHub must not sit on the critical execution path.
