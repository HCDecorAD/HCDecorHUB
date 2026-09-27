# Multi-site routing plan

Target:
- hcdecorhub.com → HCDecor HUB / Elementor
- gscsenior.hcdecorhub.com → GSC Senior experience
- amonnguyen.hcdecorhub.com → AMO NGUYEN storefront

Current verified state (2026-09-28):
- hcdecorhub.com is active on WordPress.com Atomic.
- WordPress.com manages the authoritative DNS zone.
- Hello Elementor is active.
- GSC and AMO content currently live as isolated Elementor Canvas pages on the same installation.
- No gscsenior or amonnguyen DNS records existed at verification time.

Rule:
Do not add DNS records until a real host target is selected. DNS alone cannot route two hostnames to different WordPress pages.

Preferred implementation:
1. Keep one WordPress/Elementor content installation.
2. Add a host-aware routing layer/connector that maps:
   gscsenior.hcdecorhub.com → GSC route
   amonnguyen.hcdecorhub.com → AMO route
3. Only then create DNS records pointing each subdomain at that routing target.
4. Preserve path fallbacks until subdomain SSL + routing pass verification:
   /gsc-luxury-home-v2/
   /amo-storefront-v2/

No fake products, prices, metrics, or media.
