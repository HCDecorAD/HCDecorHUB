# HCDecor HUB V2 DEMO

The demo validates architecture before any WordPress paid-plan decision.

## Demo path
1. HUB remains the operations UI.
2. A Project uses canonical ID HC-YYYY-XXXX.
3. Project maps to 01_PROJECTS and media maps to 03_MEDIA in Google Drive.
4. WordPress.com Free is used as a readable CMS through its public REST API.
5. CMS writes are reported as demo-readonly until authenticated write access is configured.
6. Review/approval remains mandatory before Web Publish.
7. Agent and Social are not allowed to bypass approval.

## Acceptance
- HUB can start if CMS is unavailable.
- CMS status clearly distinguishes read vs write capability.
- Project ID is the join key across HUB, Drive, CMS and Agent.
- No secret is committed.
- No InfinityFree dependency exists in the V2 path.
