# HCDecor Publisher Bridge

Server-side boundary for multichannel publishing.

## Queue contract
HUB V5 exports `hcdecor-publisher-queue.json` with:
- queueId / content id
- title, caption, tags
- selected channels
- scheduled local datetime
- media references
- retry attempt counter

## Required server secrets
Never place these in browser code. Configure only in the deployment environment:
- META_PAGE_ACCESS_TOKEN / META_PAGE_ID
- YOUTUBE OAuth credentials + refresh token
- TIKTOK OAuth credentials/token
- ZALO OA credentials/token

## Worker rules
1. Read only queued items.
2. Reject items not explicitly marked ready by the HUB.
3. Publish only when scheduleAt is due.
4. Use queueId as idempotency key.
5. Store per-channel result/post id.
6. Retry transient failures with capped exponential backoff.
7. Never retry authentication/permission errors indefinitely.
8. Keep an audit event for enqueue, attempt, success, failure and manual cancellation.

No credentials are committed to this repository.
