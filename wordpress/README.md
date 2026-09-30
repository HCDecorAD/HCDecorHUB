# HCDecor WordPress + Elementor

MÃ¡Â»Â¥c tiÃƒÂªu: thay Visual Builder tÃ¡Â»Â± phÃƒÂ¡t triÃ¡Â»Æ’n bÃ¡ÂºÂ±ng WordPress + Elementor Free, trong khi legacy production host hiÃ¡Â»â€¡n tÃ¡ÂºÂ¡i vÃ¡ÂºÂ«n giÃ¡Â»Â¯ nguyÃƒÂªn Ã„â€˜Ã¡Â»Æ’ rollback.

## KiÃ¡ÂºÂ¿n trÃƒÂºc
- Agent Public production: Cloudflare Workers
- Visual website mÃ¡Â»â€ºi: WordPress + Elementor Free
- Theme nÃ¡Â»Ân: Hello Elementor
- Website Data: WordPress REST API
- HCDecor HUB: tÃƒÂ­ch hÃ¡Â»Â£p REST API sau khi staging Ã¡Â»â€¢n Ã„â€˜Ã¡Â»â€¹nh

## CÃƒÂ i staging
1. TÃ¡ÂºÂ¡o WordPress staging trÃƒÂªn hosting/VPS cÃƒÂ³ PHP + MySQL.
2. CÃƒÂ i theme **Hello Elementor**.
3. CÃƒÂ i plugin **Elementor Website Builder** bÃ¡ÂºÂ£n Free.
4. Copy thÃ†Â° mÃ¡Â»Â¥c plugin `wordpress/hcdecor-core` vÃƒÂ o `wp-content/plugins/hcdecor-core`.
5. Activate **HCDecor Core**.
6. VÃƒÂ o Settings > Permalinks > Post name.
7. TÃ¡ÂºÂ¡o cÃƒÂ¡c trang: Trang chÃ¡Â»Â§, GiÃ¡Â»â€ºi thiÃ¡Â»â€¡u, DÃ¡Â»â€¹ch vÃ¡Â»Â¥, DÃ¡Â»Â± ÃƒÂ¡n, LiÃƒÂªn hÃ¡Â»â€¡.
8. DÃƒÂ¹ng Elementor dÃ¡Â»Â±ng Trang chÃ¡Â»Â§ theo `design-system.md`.

KhÃƒÂ´ng chuyÃ¡Â»Æ’n domain production cho Ã„â€˜Ã¡ÂºÂ¿n khi staging Ã„â€˜Ã¡ÂºÂ¡t kiÃ¡Â»Æ’m thÃ¡Â»Â­ Desktop/Tablet/Mobile.
