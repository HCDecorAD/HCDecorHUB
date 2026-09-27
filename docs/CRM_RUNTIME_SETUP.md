# HCDecor CRM runtime

Durable store: Google Sheets `HCDecor CRM - Leads & Projects`.

Required server-side environment variables:
- HCDECOR_CRM_SHEET_ID
- HCDECOR_CRM_SHEET_URL
- HCDECOR_CRM_WRITE_PROVIDER=google-sheets-api
- GOOGLE_SERVICE_ACCOUNT_JSON (secret; never commit)
- HCDECOR_DRIVE_PROJECTS_FOLDER_ID
- HCDECOR_DRIVE_MEDIA_FOLDER_ID

Policy: Web forms must return unavailable until authenticated server-side Google Sheets write access exists. Never simulate a successful Lead or Project write.
