# HCDecor HUB

HCDecor HUB is the Multi-Business AI Command Center and Agent/API control plane for the HCDecor ecosystem.

## Production authorities
- HCDecor public website — https://hcdecorhub.com — WordPress
- HCDecor Agent/API — https://hcdecor-hub.huycuongonline.workers.dev — Cloudflare Worker
- GSC Senior — https://gscsenior.hcdecorhub.com — GitHub Pages
- AMO Nguyen — https://amonguyen.hcdecorhub.com — GitHub Pages
- GitHub `main` — source/version/rollback authority

## Release
Source changes are validated by `.github/workflows/quality-gate.yml`. Worker production is independently checked by `production-verify.yml`. Production promotion is manual through `production-deploy.yml`, pinned to an explicitly approved current-main SHA and protected by the `production` environment.

Production mutation execution remains fail-closed until durable state and authenticated identity/approval/executor authority are bound and verified. Local `.runtime` state is non-authoritative.

## Source of truth
Read `docs/MASTER-IMPLEMENTATION.md`, `docs/PRODUCTION-OPERATIONS.md`, and `config/site-registry.json` before changing deployment or automation targets. Do not move frontend authorities or change DNS as a release workaround.
