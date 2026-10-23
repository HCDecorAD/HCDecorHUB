# TCHC Shared Agent Discovery — HOCUONG

Applies to OpenCode, OpenHands, Goose, Codex and other HCDecor agents that explicitly load this file.

## Mandatory discovery
1. Before reporting a tool absent, inspect the TCHC capability inventory and its evidence.
2. Prefer Local First and the established HCDR Relay for authorized HOCUONG discovery.
3. Relay repository: HCDecorAD/HCDecor-HCDR-Relay.
4. Job transport: GitHub issues labeled `hcdr-job`; results: issue comments with `hcdr-result/v2`.
5. Successful evidence: https://github.com/HCDecorAD/HCDecor-HCDR-Relay/issues/1849 (174 root items, 2026-10-23).
6. Historical success does not prove current health: verify per session.
7. LISTED = directory exists; runtime UNKNOWN until an executable check passes.
8. Never expose secrets, credentials, .env content or tokens. Never assume permission for production mutations.
9. Never reset or rebuild PASS components merely because an agent cannot discover them.
10. Preserve evidence URLs, timestamps and UNKNOWN states; do not invent PASS.

This file is a shared GitHub reference. It is NOT automatically injected into all ChatGPT sessions or local agent startup contexts.
