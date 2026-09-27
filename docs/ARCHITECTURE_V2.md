# HCDecor HUB V2 Architecture

Baseline: `30e64a24a2cc64691243d13c617aaed1bb9a93ba`

## Boundaries
- GitHub: source/version/rollback only.
- Vercel: Website frontend and HCDecor HUB runtime.
- Google Drive: project data, media/assets and backups.
- WordPress.com: website CMS and REST API.
- HUB: operations, review and control plane.
- Agent: AI workers, automation and orchestration.
- Social APIs: deferred until the core migration is stable.

## Project identity
Canonical format: `HC-YYYY-XXXX`.
The ID is immutable and is the join key across HUB, Drive, WordPress content and Agent jobs.

## Safety
No runtime data or secrets in GitHub. No hard-coded legacy backend. Restore each layer independently.
