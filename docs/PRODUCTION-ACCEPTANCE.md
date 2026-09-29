# HCDecor HUB - Production Acceptance
Generated: 2026-09-30

## Current production
- Agent Public: https://hcdecorhub.vercel.app/hub/agents
- Current verified production commit: 6470c38
- Public status: healthy 4/4
- Production write: false
- Monitoring: hourly
- Backup: daily 03:30
- Monitoring/backup retention: 14 days

## Candidate source
- GitHub main: f575b65
- Architecture: PASS 16/16
- Build: PASS
- Master E2E: FULL PASS
- Adds Public Safety Contract v1 and production smoke gate.
- Local Vercel/env state is ignored from Git.

## Deployment blocker
Vercel rejected a production deployment because the account/team exceeded the daily API deployment limit (more than 100 deployments/day). This is an external quota blocker, not an application failure.

## Acceptance gate after quota reset
1. Run scripts/deploy-production-once.ps1.
2. Require Vercel production deployment success.
3. Require production smoke 6/6, including /api/public/contract.
4. Verify Vercel runtime error scan is clean.
5. Only then mark Public Contract stage production PASS.

## Safety invariants
- default-deny remains enabled.
- public production_write remains false.
- mutations remain approval-required.
- secrets and local Vercel state must not be committed or exposed.
- AMO reference/demo commerce data is not verified production authority.
