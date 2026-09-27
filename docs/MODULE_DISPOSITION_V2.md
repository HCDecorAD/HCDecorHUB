# HCDecor HUB V2 - Module Disposition

| Component | Decision | Target |
|---|---|---|
| HUB UI / command center UX | KEEP | Vercel / HUB |
| Project -> Media -> Content -> Review -> Publish workflow | KEEP | HUB |
| Review / approval / audit trail | KEEP | HUB |
| Web publisher preflight / snapshot / rollback | KEEP + REFACTOR | HUB + CMS adapter |
| Project Vault data model / Drive references | KEEP + MIGRATE | Drive adapter |
| System health concepts | KEEP + REFACTOR | HUB / Agent |
| WordPress hcdecor-core monolith | REFACTOR | CMS-only plugin/modules |
| WordPress hub-dashboard / hub-suite / full-operations | MIGRATE | HUB control plane |
| Agent intake / worker lifecycle | MIGRATE | Agent |
| Automation queue / recipes | MIGRATE | Agent |
| AI provider execution | MIGRATE | Agent |
| Google Drive OAuth / vault runtime | MIGRATE | Drive service/adapter |
| CMS content models | KEEP + REFACTOR | WordPress.com |
| InfinityFree / freedev fallback | REMOVE | none |
| Hard-coded production backend URLs | REMOVE | env/config adapters |
| Duplicate WordPress control centers | REMOVE after parity | HUB |

Migration rule: remove legacy components only after functional parity is verified in the destination layer.
