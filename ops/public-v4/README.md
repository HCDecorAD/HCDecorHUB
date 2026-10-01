# HCDecor Public Website V4 Operations

Target workspace: `D:\HCDecorHUB\HCDecor_Public_Web_v4`

Safety:
- Preview deployment only.
- No `vercel --prod`.
- No modification of hcdecorhub.com WordPress production.
- QA gates run before deployment.

Recommended order:
1. 02_QA_SITE.bat
2. 03_AUDIT_LINKS.bat
3. 07_AUDIT_MEDIA.bat
4. 08_AUDIT_SEO.bat
5. 04_CHECKPOINT.bat
6. 05_DEPLOY_PREVIEW.bat
7. 06_VERIFY_PREVIEW.bat <preview-url>
