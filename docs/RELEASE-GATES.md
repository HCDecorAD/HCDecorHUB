# HCDecor HUB Release Gates

1. Read `config/site-registry.json` before targeting any site.
2. Verify workspace/site/module/action permission.
3. Keep secrets in environment storage only.
4. Development -> Preview/Staging -> Production.
5. QA must cover registry, security, regression and rollback.
6. Production publish/delete/manage is permission-gated and must write an audit event.
7. HCDecor production authority is WordPress + Elementor at hcdecorhub.com.
8. GSC and AMO remain independent frontends.

## Blocker policy
A broken automation path must not block unrelated work. Use a safe direct source path when supported; otherwise quarantine the broken automation and continue independent phases.
