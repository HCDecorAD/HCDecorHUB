# HCDecor HUB Release Gates

1. Resolve the target from `config/site-registry.json` and verify workspace/site/module/action permission.
2. Keep secrets in protected environment storage only.
3. Development -> Preview/Staging -> Production; GitHub `main` is source/version/rollback authority.
4. Source release requires Architecture, Commerce, Persistence, production build, Master Agent local E2E, and Cloudflare build PASS.
5. HCDecor Worker production promotion is manual through `production-deploy.yml`, protected by the GitHub `production` environment and pinned to an explicitly approved current-main SHA.
6. Strict production smoke runs after deploy. Independent `production-verify.yml` must continue exposing source/production drift.
7. Production mutation remains disabled until durable state and authenticated identity/approval/executor authority are bound and verified. Local `.runtime` state is non-authoritative.
8. HCDecor public website authority remains WordPress at https://hcdecorhub.com. GSC and AMO frontend authorities remain GitHub Pages. Do not change DNS or hosting authority to bypass a failed gate.
9. Production-impacting actions require auditable authority; do not fabricate an audit trail from local spool state.

## Blocker policy
A broken support-tool path must not block unrelated safe work. Quarantine the broken path and continue independent phases, but never bypass a production security, persistence, authority, or verification gate.
