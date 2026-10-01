# HCDecor Website Production Checkpoint — 2026-10-01

Scope: HCDecor public website only. Production authority: WordPress.com + Elementor. GitHub remains source/version/rollback where applicable.

## Production
- Homepage: page ID 44 — HCDecor – Design & Build
- Services: page ID 32
- Projects: page ID 33
- About: page ID 34
- Contact: page ID 35
- Public domain: hcdecorhub.com

## Completed in this checkpoint
- Public site identity changed from internal CMS/Agent naming to HCDecor | Design & Build.
- Homepage, Services, Projects, About and Contact content/SEO refreshed.
- Main pages have dedicated SEO title + description.
- Homepage mobile navigation improved.
- GSC images referenced through WordPress image CDN sizing.
- Contact page includes CoBlocks contact form: name, email, phone, project/message, submit.
- Projects page now includes verified stored HCDecor signage media: Wait Gaming and PLAY NET.
- Live crawl: main pages HTTP 200.
- No Elementor upgrade, no HCDecor Runtime modification.
- No Supabase/Vercel added to HCDecor website architecture.
- Legacy GSC/AMO/WooCommerce pages left unchanged pending role verification.

## Media note
WordPress media connector returned an upstream response error after binary writes, but Media Library confirmed successful creation. Duplicate copies exist for Wait Gaming and PLAY NET from retry attempts. Do not delete without a separate confirmed cleanup action.

## Remaining
- Add more verified interior/signage media from Drive when binary upload path is stable.
- Choose/crop a HCDecor-only favicon/logo asset; current available logo is HCDecor HUB.
- Validate real contact-form delivery separately without generating test spam during automated QA.
- Investigate frontend locale en-US vs WordPress language vi; TranslatePress is active, so avoid blind plugin changes.
- Review legacy public URLs before any noindex/draft/delete action.

## Guardrails
Do not change HCDecor HUB architecture from this website task. WordPress.com remains production authority. Do not move HCDecor production to Vercel/Supabase. Do not fabricate company/project data.
