# HCDecor HUB — Clean Build

## Production architecture
- `/`: public HCDecor website. Elementor 14:03 visual baseline, VN/EN, contact icons.
- `/hub`: isolated operations application.
- WordPress: CMS / public content API only during clean-build phase.
- External OAuth/provider publishing and InfinityFree admin actions: intentionally deferred.

## Clean HUB workflow
Content Studio → Review → Ready → Automation Queue → Web Publish. Projects are read from the WordPress public API. Media and workflow drafts persist locally in the browser until a production datastore is introduced.

## Truth rules
No fabricated KPI, no fake connection state, no claim of external publishing. System Health reports runtime/API state only.

## Release isolation
Public website UI and HUB app must be changed independently. Restoring Elementor files must never roll back `app/hub` or operational modules.
