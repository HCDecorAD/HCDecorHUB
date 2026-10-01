# HC Agent Control V1 checkpoint

## Authority
Master chat: HC Agent Control.
Local canonical runtime target: D:\\HCDecorHUB\\HC_AutoChat.
GitHub branch: hc-agent-control-v1 is source/patch staging until local relay workspace is available.

## Verified before staging
- CP0 preflight/bootstrap PASS.
- Discovery/identity/registry/CDP automated tests PASS.
- Managed Edge launch/recovery PASS.
- Real CDP ChatGPT root page detection PASS.
- CP2 dry-run routing tests PASS.
- Last full local automated suite: 10/10 PASS.

## Gates intentionally open
- CP1 live multi-conversation /c/<conversation_id> detection not yet proven.
- Alias re-detect after browser restart not yet proven live.
- Real targeted send is disabled.
- G2 Targeted Send is not PASS.

## Current patch
- conversation_id is primary identity authority.
- changed CDP target_id with same conversation_id is allowed and reported as target_changed.
- title-only identity is blocked.
- missing conversation_id is blocked.
- cp1_probe writes logs/cp1-real-cdp.json and never sends.

## Next execution
1. Sync patch into D:\\HCDecorHUB\\HC_AutoChat.
2. Run CP1 probe and full unit suite.
3. Open at least two real ChatGPT /c/ conversations in managed Edge.
4. Bind aliases by conversation_id and verify restart re-detection.
5. Only then implement/arm real DOM send with pre-submit and post-submit verification.
