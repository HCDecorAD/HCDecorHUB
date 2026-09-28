# HCDecorHUB Production Frontend Modules

Fixed production architecture:

| Website | Production host | Role |
|---|---|---|
| HCDecor HUB | https://hcdecorhub.com | Main studio/HUB |
| GSC | https://gscsenior.hcdecorhub.com | Independent GSC website |
| AMO NGUYEN | https://amonnguyen.hcdecorhub.com | Independent AMO website |

Rules:
- Keep source, content, navigation and deployment independent per website.
- Never route GSC or AMO through HCDecor page paths in production.
- Social links remain verified-only; Zalo is currently verified.
- GSC Digital Twin keeps 11 configured hotspots; only hotspots with real media open video.
- AMO catalog publishes only real products/prices/stock.
- DNS/server targets must use the previously assigned IPs; never guess IP values.
