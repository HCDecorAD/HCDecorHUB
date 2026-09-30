# HCDecor HUB - Production Acceptance
Updated: 2026-09-30

## Agent Public production
- Platform: Cloudflare Workers
- Worker: https://hcdecor-hub.huycuongonline.workers.dev
- Runtime: Next.js 16 via OpenNext Cloudflare adapter
- Cloudflare Worker version verified during migration: f420c740-6d9b-41b0-880d-6caebc8f97d8
- Agent /hub/agents: 200
- Public contract v1.1: 200
- Runtime: 200
- Master readiness: 200
- Production write: false
- Default deny: enabled
- Mutations: approval-required

## Workspace migration state
- HCDecor public website authority remains WordPress at https://hcdecorhub.com.
- GSC target provider is Cloudflare; custom domain currently still resolves to a legacy origin and requires DNS/origin migration verification.
- AMO target provider is Cloudflare; custom domain DNS is not yet active.
- HC Shop Engine remains live on Cloudflare Workers; commerce reference/demo data is not verified production authority.

## Release gate
1. npm run test:architecture
2. npm run cf:build
3. npx wrangler deploy
4. scripts/production-smoke.ps1
5. Preserve default-deny, approval guards and production_write=false.
