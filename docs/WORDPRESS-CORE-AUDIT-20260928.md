# HCDecor WordPress Core Audit — 2026-09-28

## Existing source retained
The repository already contains a substantial wordpress/hcdecor-core plugin and module suite. New implementation must extend this source instead of creating a duplicate WordPress core.

Observed capabilities: project/service content types, REST endpoints, Elementor homepage builder, lead/CRM foundation, HUB dashboard, AI workspace/providers, automation/workflow engine, connection center, content operations, backup/restore, Drive inbox/vault, media manager/intelligence, project publishing/vault, publishing runtime, social connectors, system health and web publisher.

## Canonical integration rule
The config files for site registry, permissions, audit schema, data model, integrations and QA gates define the cross-business control plane. The WordPress plugin is the HCDecor runtime adapter and must not become a second independent source of truth.

## Production rule
No automatic site-wide WordPress write from repository inspection. Production writes require current-state read, rollback readiness, and explicit WordPress write confirmation.
