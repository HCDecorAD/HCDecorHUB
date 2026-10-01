# HCDecor HUB — Current State

## Production authority map
- HCDecor public website: https://hcdecorhub.com — WordPress.
- HCDecor Agent/API control plane: Cloudflare Worker `hcdecor-hub`.
- GSC: https://gscsenior.hcdecorhub.com — GitHub Pages.
- AMO: https://amonguyen.hcdecorhub.com — GitHub Pages.
- GitHub `main`: source/version/rollback authority.
- Commerce live-data authority: HC Shop Engine.

## Non-negotiable release rules
1. Resolve targets from `config/site-registry.json`.
2. GitHub `main` is source/version/rollback authority.
3. Never move frontend hosting or DNS to hide a release failure.
4. Production deploy is exact-SHA and protected by the `production` environment.
5. Architecture, Commerce, Persistence, production build, Master Agent E2E and Cloudflare build gates must PASS.
6. Independent Production Verify must PASS after deployment.
7. Production mutation remains disabled until durable state and authenticated identity/approval/executor authority are bound and verified.
8. Local `.runtime` state is diagnostic only and never production authority.

## Current release state
- Current main: this document is maintained against the current `main` branch; use the latest successful Quality Gate run as the release gate authority.
- Latest release candidate recorded in `docs/RELEASE-CANDIDATE-5897a78c.md`: `5897a78c` was superseded by subsequent documentation-only release-state commits; no production deploy is authorized from historical SHAs.
- Latest independent Production Verify for current SHA: **FAIL**.
- Failing production check: `/api/public/contract`.
- Other production smoke endpoints in that run: **PASS**.
- Classification: deployed Worker contract drift / stale Worker deployment, not a source Quality Gate failure.
- Production promotion: **BLOCKED**.

## Immediate execution
1. Dispatch `HCDecor HUB Production Deploy` with the exact current-main SHA above.
2. Run the complete build, deploy and strict smoke gate.
3. Require independent Production Verify to PASS.
4. Record deployed SHA and verification result as the production baseline.
5. If deployment cannot be dispatched, do not change source merely to bypass the blocker.

## Foundation upgrade tracks
### Durable state
- Source boundary: implemented and fail-closed.
- Production provider: unbound.
- Local spool: diagnostic/non-authoritative.
- Mutation execution: disabled.

### Identity / RBAC
- Source boundary: implemented and default-deny.
- Production identity provider: unbound.
- Authenticated executor/approval authority: not yet bound.

### Operations / DR
- Local backup integrity: available.
- Off-device production backup: outstanding.
- Restore test: outstanding.
- Centralized durable monitoring/history: outstanding.
- RPO/RTO: outstanding.
- Rollback procedure: documented; executable automation remains a follow-up.

## Core workflow
Customer -> Lead -> Project -> Media -> Design/AI/Content -> Review -> Approve -> Publish -> Social -> Report

## Core modules
Registry, Business Profile, Workspace, Website, Users/Roles/Permissions, CRM, Projects, Media, Content, Publishing, AI Agents, Integrations, Reports, Audit.

## Release discipline
This file is the current-state document only. Historical SHA/checkpoint notes belong in release records, not here.
