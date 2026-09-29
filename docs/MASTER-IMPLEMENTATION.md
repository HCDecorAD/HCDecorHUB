# HCDecor HUB — Source of Truth

## Role
HCDecor HUB is the Multi-Business AI Command Center and the public HCDecor website.

## Production map
- HCDecor: https://hcdecorhub.com — WordPress + Elementor.
- GSC: https://gscsenior.hcdecorhub.com — independent GitHub Pages frontend, repo HCDecorAD/GSC.
- AMO: https://amonguyen.hcdecorhub.com — independent GitHub Pages frontend, repo HCDecorAD/AMONguyen.

## Non-negotiable rules
1. Read `config/site-registry.json` before automation targets a website.
2. Do not move HCDecor production to Vercel.
3. GSC and AMO source remain independent from HCDecor frontend source.
4. GitHub is code/version/rollback authority; Drive is media/data/backup; production runtimes stay platform-specific.
5. No secret values in GitHub. Use environment/secret storage.
6. Development -> Preview/Staging -> Production.
7. Production Publish/Delete/Manage actions require explicit permission.
8. Legacy Vercel/Next files in this repository are reference material until archived deliberately; they are not HCDecor production authority.

## Core modules
Registry, Business Profile, Workspace, Website, Users/Roles/Permissions, CRM, Projects, Media, Content, Publishing, AI Agents, Integrations, Reports, Audit.

## Workflow
Customer -> Lead -> Project -> Media -> Design/AI/Content -> Review -> Approve -> Publish -> Social -> Report.

## Current implementation phase
Phase 01 Inventory & Freeze completed baseline branch:
`backup/pre-master-implementation-20260928`

Phase 02 starts with Registry + Business Profile. Missing owner values remain null; never fabricate them.
