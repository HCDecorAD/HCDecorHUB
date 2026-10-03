# AutoWatch Snapshot

Read-only source snapshot area for HC Sentinel analysis/reuse.

Source target:
`D:\HCDecorHUB\HC_AutoChat`
branch: `hc-agent-control-v1-next`

Rules:
- Never modify the live AutoWatch runtime from this folder.
- Snapshot must include manifest + SHA256 verification.
- Exclude secrets, tokens, browser profiles, live DBs, active queues and lock files.
- Reuse patterns, not live state.
