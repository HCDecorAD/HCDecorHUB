# iMaster / HCDecorHUB — Agent Operating Rules

This repository's authoritative workflow for HOCUONG relay stalls, failed BAT, blocked jobs, Builder testing and release is [`skills/hcdr-autorecovery/SKILL.md`](skills/hcdr-autorecovery/SKILL.md).

## Mandatory for agents working in this repository
- Read and apply that skill **before** operating HCDR Relay, iMaster Transport Mesh, HC Visual Builder or release tasks.
- Follow CHECK → ANALYZE → reuse/create BAT → GitHub → run/verify → up to 4 independent workers → FIX → RETEST → release gate.
- Preserve existing PASS, never fake execution, never bypass allowlists/owner lock/deploy gate; no autonomous production publication.
- Report exact GitHub issue, commit and execution evidence. If remote execution is blocked, mark BLOCKED and request only the single essential owner action.
- Do not treat AGENTS.md as proof the live iMaster runtime has loaded this file; verify local loader registration and a real runtime test before marking HARD-INTEGRATED PASS.
