# HCDecor HUB Host Router

Maps brand subdomain root requests to existing live brand pages while preserving one WordPress/Elementor installation.

Mappings:
- gscsenior.hcdecorhub.com → /gsc-luxury-home-v2/
- amonnguyen.hcdecorhub.com → /amo-storefront-v2/

Safety:
- Admin, AJAX and REST requests are ignored.
- Non-root paths are not rewritten.
- Uses temporary 302 during migration; change to 301 only after DNS, SSL and host mapping are verified.
- DNS must not be created until WordPress.com accepts both hostnames for the Atomic site.
