# HCDecor durable runtime setup

The Master Agent reports runtime capability as booleans only. Secrets must remain server-side and must never be committed.

## WordPress
- HCDECOR_WP_BASE_URL=https://hcdecorhub.com
- HCDECOR_WP_API_TOKEN (secret)

## CRM / Google Sheets
- HCDECOR_CRM_WRITE_PROVIDER=google-sheets-api
- HCDECOR_CRM_SHEET_ID
- GOOGLE_SERVICE_ACCOUNT_JSON (secret)

## Google Drive
- HCDECOR_DRIVE_ROOT_FOLDER_ID
- HCDECOR_DRIVE_PROJECTS_FOLDER_ID

## Verified non-secret data authorities
- CRM Sheet ID: `1vZcpqZETYAticin2MyeO9UeJgMpDBYMEV16J1o6ouSg` (`Leads`, `Projects` schema verified)
- Business Drive root ID: `1NqJuLhum63XVea8wPjZtTETl29bm3U7r` (`HCDecor Hub`)
- Projects folder ID: `1bKNzYBoDQ-FKb0KegVk1NUinmvV6B5xy` (`01_PROJECTS`)
- Master Database ID: `1UM6hs6yZevy-DNzUTvfirTKGo-zIgTIv3tYw-TPxCts` (`SAFE_INTERNAL` control/data plane)
- These identifiers are not credentials. Chat connector authorization does not make the HCDecor server runtime writable.

## Protected project provisioning
- HCDECOR_PROJECT_API_TOKEN (secret)

Capability contract:
- cmsRead requires HCDECOR_WP_BASE_URL.
- cmsWrite requires HCDECOR_WP_API_TOKEN.
- driveConfigured requires HCDECOR_DRIVE_ROOT_FOLDER_ID.
- driveWrite requires service account + Drive root.
- leadWrite requires google-sheets-api + service account + CRM sheet.
- projectWrite additionally requires projects folder + project API token.

Policy: web forms and Master Agent durable writes must report unavailable until authenticated server-side access exists. Never simulate a successful Lead, Project, Drive, or WordPress write. Runtime readiness is not production health and does not grant production-write authority.
