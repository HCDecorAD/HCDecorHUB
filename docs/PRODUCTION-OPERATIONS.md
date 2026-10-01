# HCDecor HUB Production Operations

## Authority
- HCDecor public website: WordPress at https://hcdecorhub.com
- Agent/API control plane: Cloudflare Worker
- GSC and AMO public frontends: GitHub Pages
- GitHub main: source/version/rollback authority

## Release gate
A release is eligible only after Architecture Contract, Commerce Contract, Next production build, and Cloudflare build pass. Production mutation remains approval-required.

## Production verification
After deployment run the production smoke and health snapshot scripts. Verify /api/public/contract reports contract 1.1, production_write=false, default_deny=true, commerce=engine-live-data-verified, and both blockers true.

## Incident policy
Two consecutive failed health snapshots establish confirmed degradation. Do not change DNS or hosting authority as an incident shortcut. Roll back the Worker/source release when a release regression is confirmed.

## Persistence
Local .runtime files are non-authoritative spool/cache only. Production run, approval, workflow and audit history must use durable provider storage before mutation execution can be considered production-grade.

## 2026-10-01 deployment action
- Approved source SHA: `36f9c32a3d388d0fc64dd34a0a2a9e4e3cfd46e2`.
- Quality Gate: PASS.
- Production Verify: FAIL at `/api/public/contract`; deployed Worker remains behind source contract.
- Required action: manual Production Deploy dispatch with the exact SHA above; no source or gate bypass.

## Current production blocker
- Independent Production Verify continues to report the deployed Worker contract behind the source contract. This is a deployment/state-drift issue, not a reason to weaken source gates or alter frontend authority.
- Promotion remains gated until the deployed Worker satisfies the production contract.

## Backup and restore
Local backup is supplementary only. Production recovery requires off-device backup and a tested restore procedure. Never store credentials in backup artifacts or GitHub.

## Security
Default deny, exact workspace/site/module grants, explicit approval for production mutation, no test-mode production writes, no secrets in source, audit every production-impacting action.


## Deployment control
Production deploy is manual and exact-SHA only. Never dispatch production deployment while the Quality Gate for that SHA is pending or failed. A failed independent Production Verify must remain visible and blocks promotion until the deployed Worker satisfies the contract.

## Durable execution gate

Production mutation execution remains disabled until a concrete durable state provider and authenticated identity/approval authority are bound and verified. Local `.runtime` run and approval spools are diagnostic/development state only and are never production authority. Setting an environment flag alone must not make readiness or runtime report durable execution as available. Approval POST is fail-closed until authenticated durable approval is implemented.

Release quality requires architecture, commerce, persistence, production build, Master Agent local E2E, and Cloudflare build gates to pass. Independent production verification must validate the deployed Worker separately from source CI.
