# HCDecor HUB — Future Architecture Reference

**Recorded:** 2026-10-01  
**Baseline approved:** 2026-09-30  
**Status:** Reference for future upgrades. This document does not authorize changing current production authorities.

## Target evolution

```text
                         ┌──────────────────────────┐
                         │       GitHub main        │
                         │ Source / Version /       │
                         │ Release / Rollback       │
                         └────────────┬─────────────┘
                                      │
                         Quality / Security Gates
                                      │
              ┌───────────────────────┼───────────────────────┐
              │                       │                       │
              ▼                       ▼                       ▼
      HCDecor Website           HCDecor HUB              Storefronts
       WordPress.com         Agent / API Layer
              │                       │                  ┌─────┴─────┐
              │                Cloudflare Worker         │           │
              │                       │                 GSC         AMO
              │                       │                  │           │
              │                       │                 GitHub Pages
              │                       │                              │
              │                       │                        Shop Engine
              │                       │                              │
              └───────────────┬───────┴──────────────┬───────────────┘
                              │                      │
                        DATA / MEDIA             OPERATIONS
                              │                      │
                     Drive / DB / Storage      Audit / Logs /
                                             Health / Backup
```

## Locked current authorities

- HCDecor public website: WordPress.com at `hcdecorhub.com`.
- HCDecor HUB Agent/API/control plane: Cloudflare Worker.
- GSC frontend: GitHub Pages at `gscsenior.hcdecorhub.com`.
- AMO frontend: GitHub Pages at `amonguyen.hcdecorhub.com`.
- GitHub `main`: source/version/release checkpoint/rollback authority.
- Shop Engine: commerce/live-data backend authority for AMO.
- Local HOCUONG machine: development/test/tooling only; never production authority.

## Upgrade direction

Preserve the authorities above. Future architecture work should add, in order: durable HUB state; authenticated identity and RBAC; durable approval/execution/audit; centralized operations/observability; off-device backup and tested disaster recovery.

Local `.runtime` state is diagnostic/development only. Production mutation stays fail-closed until durable state and authenticated approval/executor authority are bound and verified.

## Change-control rule

Do not move HCDecor, GSC, or AMO frontend authority; do not change root DNS/nameserver; and do not use a hosting migration as a workaround for a failed release gate. Any future architecture migration requires an explicit architecture decision and rollback plan.
