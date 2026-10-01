# HCDecor HUB — Source of Truth

## Role
HCDecor HUB is the Multi-Business AI Command Center. The HCDecor public website is a separate WordPress production authority.

## Production map
- HCDecor public website: https://hcdecorhub.com — WordPress.
- HCDecor Agent/API control plane: Cloudflare Worker `hcdecor-hub`.
- GSC: https://gscsenior.hcdecorhub.com — GitHub Pages, repository `HCDecorAD/GSC`.
- AMO: https://amonguyen.hcdecorhub.com — GitHub Pages, repository `HCDecorAD/AMONguyen`.
- Commerce live-data authority: HC Shop Engine; health endpoint `/api/health`.

## Non-negotiable rules
1. Resolve the target from the registry before automation acts on a workspace/site.
2. GitHub `main` is source/version/rollback authority.
3. Cloudflare is Agent/API control-plane authority; it does not replace HCDecor WordPress or the GSC/AMO GitHub Pages frontends.
4. No secret values in GitHub. Use protected environment/secret storage.
5. Production release must pass Architecture, Commerce, Persistence, production build, Master Agent local E2E and Cloudflare build gates.
6. Production Worker promotion uses the gated `production-deploy.yml` workflow and must pass strict production smoke.
7. Production mutation remains disabled until durable state plus authenticated identity/approval/executor authority are implemented and verified.
8. Local `.runtime` run/approval state is diagnostic only and is never production authority.
9. Do not weaken verification, change DNS, or move frontend hosting to hide a release failure.

## Runtime safety state
The public contract declares durable provider required, local spool non-authoritative, and mutation execution disabled. Approval decision and approval execution API surfaces fail closed until authenticated durable authority exists. An environment flag alone cannot enable durable execution.

## Next executable work
1. Resolve live Worker/source contract drift through the approved exact-SHA production deployment workflow after Quality Gate PASS.
2. Re-run independent Production Verify and require contract PASS before declaring production promotion complete.
3. Keep durable provider, identity provider, off-device DR and restore testing unbound/not-authorized until their prerequisites are actually available.

## Core modules
Registry, Business Profile, Workspace, Website, Users/Roles/Permissions, CRM, Projects, Media, Content, Publishing, AI Agents, Integrations, Reports, Audit.

## Workflow
Customer -> Lead -> Project -> Media -> Design/AI/Content -> Review -> Approve -> Publish -> Social -> Report.

## Upgrade checkpoints
- Durable Provider boundary: source-ready, unbound, fail-closed.
- Identity/RBAC boundary: source-ready, unbound, default-deny.
- Operations/DR: documented; off-device backup, restore test, centralized monitoring, and RPO/RTO remain outstanding.

## Current operational posture
- Source-side safety boundaries are implemented and regression-gated.
- Durable and identity providers remain intentionally unbound; no production credentials are present.
- Production Worker promotion remains blocked by independent live contract verification until the deployed Worker matches the approved source contract.

## Gate status — 2026-10-01
- Quality Gate: **PASS** for current source.
- Independent Production Verify: **FAIL** on deployed Worker contract drift.
- Therefore the source is releasable by quality gates, but production promotion is not complete.

## Immediate operator handoff
- Current main: `5db3a3a21c5ad2a6b7eeb7cc4dfda862056fe072`.
- Previous approved source `36f9c32` passed Quality Gate but remains undeployed in the live Worker.
- After current Quality Gate PASS, dispatch `HCDecor HUB Production Deploy` with the exact current-main SHA, then require Production Verify PASS.

## CI queue note
- Documentation-only commits can cancel a prior Quality Gate and start a new queued gate; no production action is authorized from a queued/cancelled gate.
- The operator must wait for the current-main SHA Quality Gate to finish PASS before dispatching production deployment.

## Quality Gate result — 2026-10-01
- Current SHA `714eebadf10a99c8cf5dc3ea25e534680402f959`: **PASS**.
- Architecture, commerce, persistence, production build, local Master Agent E2E, and Cloudflare build all passed.
- Next action remains manual exact-SHA Production Deploy, followed by independent Production Verify.

## Release gate note
- Documentation checkpoint commit `462c07f9` supersedes the previously verified source SHA.
- The current-main Quality Gate must PASS again before any production deployment dispatch.
- No production deploy is triggered automatically from this documentation update.

## Current release candidate
- Current main: `292d47f6c1d2721efb1ed5f374a6ef7b28a9e5c0`.
- Quality Gate: **PASS**.
- Production deployment is still pending manual exact-SHA dispatch.
- After deployment, independent Production Verify must PASS before production promotion is considered complete.

## Release status
Source is validated continuously by `quality-gate.yml`. Production state is independently validated by `production-verify.yml`; a failed production verification must remain visible when deployed production is behind source.
