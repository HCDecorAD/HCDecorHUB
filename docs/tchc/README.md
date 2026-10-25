# TCHC — Tool & Capability Discovery

Canonical discovery rules: [AGENTS.md](./AGENTS.md)

Evidence-backed HOCUONG root scan: https://github.com/HCDecorAD/HCDecor-HCDR-Relay/issues/1849

Deeper scans: issues #1850–#1862 in HCDecorAD/HCDecor-HCDR-Relay.

Status: GitHub reference published; automatic local installation, startup loading and cross-chat injection remain UNVERIFIED.

Agents should load this document at startup via their supported configuration or workspace instructions. No tool should claim it is installed merely because this document exists.

## Quick bootstrap for a new agent/chat
TCHC is the HCDecor Hub Tool & Capability Registry. Read [registry.json](./registry.json) for discovered tools and evidence, [AGENTS.md](./AGENTS.md) for rules, and the [repository root AGENTS.md](../../AGENTS.md) for mandatory implementation policy. Key capabilities: HCDR Relay (HOCUONG job transport), iMaster Mesh (local execution coordination), Desktop V2 Universal Control (18 registered desktop observation/action tools; screenshot historically verified). Discover additional modules from the registry; do not assume runtime PASS from LISTED or REGISTERED.

**Code rules:** prioritize the assigned product; reuse downloaded source and existing UI; edit/extend rather than rebuilding; preserve PASS modules; deliver full functionality; minimum check → BAT only if needed → independent parallel work → integrate → end-to-end test → fix/retest until evidence-backed DONE. Respect approval and production security gates. Do not run a full-system audit at every chat start.

**ChatGPT scope:** a GitHub document does not automatically inject instructions into every chat. Put this short bootstrap in the relevant Project Instructions for automatic availability within that Project; use a new chat to confirm the context is present.
