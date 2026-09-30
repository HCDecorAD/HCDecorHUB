# HCDecor HUB Release Runbook

## Production authorities
- HCDecor public website: WordPress at https://hcdecorhub.com
- HCDecor Agent/API control plane: Cloudflare Worker `hcdecor-hub`
- GSC public frontend: GitHub Pages at https://gscsenior.hcdecorhub.com
- AMO public frontend: GitHub Pages at https://amonguyen.hcdecorhub.com
- GitHub `main`: source/version/rollback authority

## Release gate
1. Resolve Workspace and Site from Registry.
2. Confirm repository/runtime authority and permissions.
3. Create or confirm rollback point.
4. Pass Architecture, Commerce and Persistence contracts.
5. Pass production build, Master Agent local E2E and Cloudflare build.
6. Production promotion only through `.github/workflows/production-deploy.yml` using the GitHub `production` environment.
7. The deploy workflow must run strict production smoke after Wrangler deploy.
8. Independent `production-verify.yml` remains strict and must expose source/production drift rather than masking it.

## Safety boundary
Production mutation execution remains disabled until a concrete durable state provider and authenticated identity/approval authority are bound and verified. Local `.runtime` files are non-authoritative. An environment flag alone is never durable execution authority. Do not change DNS, frontend hosting authority, or weaken smoke checks to make a release pass.

## Site rules
### HCDecor
WordPress remains public website authority. The Next.js/Cloudflare Worker is the Agent/API control plane, not the HCDecor public website replacement.

### GSC
Repository `HCDecorAD/GSC`; GitHub Pages is production frontend authority. Preserve the 11 hotspot contract; hotspots 1–10 use configured videos and hotspot 11 has no video.

### AMO
Repository `HCDecorAD/AMONguyen`; GitHub Pages is production frontend authority at https://amonguyen.hcdecorhub.com. Never fabricate products, prices, stock, brands, reviews, or order history. Commerce data authority is the verified Shop Engine.
