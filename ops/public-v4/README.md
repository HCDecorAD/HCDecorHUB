# HCDecor Website Operations

Production architecture:
- HCDecor main website: WordPress / Elementor.
- HCDecor Public V4 static workspace: design prototype and migration source only.
- GSC and AMO remain separate GitHub Pages websites.
- Vercel is not part of HCDecor website architecture.

V4 source workspace:
`D:\HCDecorHUB\HCDecor_Public_Web_v4`

Safe workflow:
1. Audit/checkpoint static V4 design source.
2. Backup WordPress before mutations.
3. Rebuild approved sections in WordPress/Elementor.
4. Verify desktop/mobile, links, forms, media, SEO.
5. Publish only after WordPress QA.

Never run Vercel deployment for HCDecor.
