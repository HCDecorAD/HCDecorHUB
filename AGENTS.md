# iMaster / HCDecorHUB — Agent Operating Rules

This repository's authoritative workflow for HOCUONG relay stalls, failed BAT, blocked jobs, Builder testing and release is [`skills/hcdr-autorecovery/SKILL.md`](skills/hcdr-autorecovery/SKILL.md).

## Mandatory for agents working in this repository
- Read and apply that skill **before** operating HCDR Relay, iMaster Transport Mesh, HC Visual Builder or release tasks.
- Follow CHECK → ANALYZE → reuse/create BAT → GitHub → run/verify → up to 4 independent workers → FIX → RETEST → release gate.
- Preserve existing PASS, never fake execution, never bypass allowlists/owner lock/deploy gate; no autonomous production publication.
- Report exact GitHub issue, commit and execution evidence. If remote execution is blocked, mark BLOCKED and request only the single essential owner action.
- Do not treat AGENTS.md as proof the live iMaster runtime has loaded this file; verify local loader registration and a real runtime test before marking HARD-INTEGRATED PASS.

## TCHC — Mandatory capability discovery
- TCHC means Tool & Capability Registry for HOCUONG / HCDecorHUB.
- Before claiming a tool is unavailable or rebuilding it, read [`docs/tchc/AGENTS.md`](docs/tchc/AGENTS.md) and [`docs/tchc/README.md`](docs/tchc/README.md).
- Consult HCDR Relay evidence and existing Desktop V2 Universal Control (18 registered tools, screenshot verified); distinguish REGISTERED, LISTED, PASS and UNKNOWN.
- Canonical HCDR transport: `HCDecorAD/HCDecor-HCDR-Relay` (GitHub issues `hcdr-job`, results `hcdr-result/v2`).
- This repository instruction applies only when the agent loads it; it does not auto-inject TCHC into unrelated ChatGPT chats. Verify local startup loading separately.

## HARD RULE — Execute assigned product work first (owner directive 2026-10-25)
- Focus on the actual assigned deliverable and completed product, not repeated discovery, circular checks, or rebuilding infrastructure.
- Reuse existing downloaded source code, previously developed UI, components, assets and working modules. EDIT/EXTEND the existing implementation; do not create a new app, project, UI or replacement implementation without explicit owner approval.
- Deliver the full requested feature set and preserve working PASS functionality. Do not silently reduce scope to a demo, stub or placeholder.
- Execution sequence: identify the minimum necessary changes → reuse an existing BAT (create a targeted BAT only if needed) → implement in parallel across independent workstreams when safe → integrate into the existing system → run end-to-end flow tests → FIX → RETEST until DONE or a concrete blocker is evidenced.
- Avoid redundant prechecks. Do not repeatedly retest historical PASS items unless required by changed dependencies or the end-to-end acceptance flow.
- Prefer implementation and integration before broad system-wide audits; verify the actual user journey after integration.
- Parallel workers must avoid conflicting writes, share one canonical source, and merge/review results before marking DONE.
- Do not delete/reset/overwrite PASS modules or existing owner work. Never fabricate PASS, claim an integration completed without evidence, or bypass security gates, owner approvals, production-release restrictions, or access controls. Where a release gate requires approval, prepare the complete deliverable and report the exact blocker rather than silently publishing.
- Status reporting must distinguish IMPLEMENTED, INTEGRATED, TESTED, DONE and BLOCKED, with concrete evidence.
