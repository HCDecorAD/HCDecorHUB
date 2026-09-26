# HCDecor WordPress + Elementor

Mục tiêu: thay Visual Builder tự phát triển bằng WordPress + Elementor Free, trong khi production Vercel hiện tại vẫn giữ nguyên để rollback.

## Kiến trúc
- Production hiện tại: Vercel (không thay đổi)
- Visual website mới: WordPress + Elementor Free
- Theme nền: Hello Elementor
- Website Data: WordPress REST API
- HCDecor HUB: tích hợp REST API sau khi staging ổn định

## Cài staging
1. Tạo WordPress staging trên hosting/VPS có PHP + MySQL.
2. Cài theme **Hello Elementor**.
3. Cài plugin **Elementor Website Builder** bản Free.
4. Copy thư mục plugin `wordpress/hcdecor-core` vào `wp-content/plugins/hcdecor-core`.
5. Activate **HCDecor Core**.
6. Vào Settings > Permalinks > Post name.
7. Tạo các trang: Trang chủ, Giới thiệu, Dịch vụ, Dự án, Liên hệ.
8. Dùng Elementor dựng Trang chủ theo `design-system.md`.

Không chuyển domain production cho đến khi staging đạt kiểm thử Desktop/Tablet/Mobile.
