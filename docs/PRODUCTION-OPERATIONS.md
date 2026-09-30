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

## Backup and restore
Local backup is supplementary only. Production recovery requires off-device backup and a tested restore procedure. Never store credentials in backup artifacts or GitHub.

## Security
Default deny, exact workspace/site/module grants, explicit approval for production mutation, no test-mode production writes, no secrets in source, audit every production-impacting action.
