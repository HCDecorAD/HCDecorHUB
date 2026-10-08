# HC Website Factory v1 — STAGING

Scope: WordPress + Astra + Elementor + WooCommerce. AMO first, GSC second. Do not modify production or DNS. Plugin First / Local First. Scripts are staged, not verified as executed.

HOCUONG: D:\HCDecorHUB\HC Website Factory

1. Run HC-Website-Factory-Setup.bat
2. Run PowerShell -NoProfile -ExecutionPolicy Bypass -File Download-HC-Factory-Official.ps1
3. Validate downloads SHA256 and archive contents before any install.
4. Create isolated AMO local WordPress staging, not on live site.
5. Test admin role, product CRUD, Elementor page edit, backup/restore, then PASS.

Do not commit credentials, WordPress database dumps, client data, or commercial ZIPs.