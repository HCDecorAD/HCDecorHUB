# TransWarp v0.4 — Cross-Chat Local Capability

## Purpose
Any new chat must be able to identify TransWarp and decide whether it can execute on HOCUONG without relying on HCDR as the primary transport.

## Canonical discovery
Read:
- `config/transwarp-local-capability.json`
- `docs/TRANSWARP-CROSS-CHAT-BOOTSTRAP.md`

Never infer local capability from the chat title. iMaster is only a chat group.

## Required handshake for every new chat
1. Resolve the capability manifest from HCDecorAD/HCDecorHUB.
2. Detect whether the current chat/session has an authorized local connector/Desktop/Computer route.
3. If direct local route exists:
   - health check P11 ingress;
   - submit `transwarp-local/job-v1`;
   - read evidence from Local Runner;
   - GitHub is sync only.
4. If no direct local route exists:
   - return `TRANSWARP_LOCAL_CONNECTOR_REQUIRED`;
   - do not pretend Local is reachable;
   - do not downgrade silently to HCDR.
5. HCDR may be used only when explicitly chosen as fallback/recovery.

## Route order
DIRECT_LOCAL_INGRESS -> LOCAL_RUNNER_ONESHOT -> HCDR_FALLBACK

## Important limitation
A normal chat cannot reach 127.0.0.1 on HOCUONG by itself. The chat needs an authorized Desktop/plugin/connector bridge. P11 provides the local endpoint; the bridge is the missing transport between ChatGPT and localhost.

## Canonical prompt for a new chat
`Use TransWarp v0.4 Local-First. Resolve config/transwarp-local-capability.json first. If an authorized direct local connector is available, health-check P11 and dispatch jobs locally. Do not use HCDR unless fallback is explicitly required. If no direct local connector exists, report TRANSWARP_LOCAL_CONNECTOR_REQUIRED.`

## State markers
- `TRANSWARP_LOCAL_CAPABILITY_OK`
- `TRANSWARP_LOCAL_CONNECTOR_REQUIRED`
- `TRANSWARP_HCDR_FALLBACK_ACTIVE`
