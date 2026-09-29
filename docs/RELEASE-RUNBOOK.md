# HCDecor HUB Release Runbook

## Gate
1. Resolve Workspace and Site from Registry.
2. Confirm repository/runtime authority.
3. Verify permissions.
4. Create or confirm rollback point.
5. Build/preview.
6. QA responsive + critical flows.
7. Production promotion only after gate passes.
8. Verify public URL.
9. Write audit event.

## Site rules
### HCDecor
Production is WordPress + Elementor at https://hcdecorhub.com. GitHub Next/Vercel source is reference/command-center source, not production authority.

### GSC
Repository HCDecorAD/GSC. Preserve 11 hotspots. Hotspots 1–10 use configured real videos; hotspot 11 has no video. Never replace the large index blindly.

### AMO
Repository HCDecorAD/AMONguyen. Production hosting is GitHub Pages at https://amonguyen.hcdecorhub.com. Never fabricate products, prices, stock, brands, reviews, or order history.
