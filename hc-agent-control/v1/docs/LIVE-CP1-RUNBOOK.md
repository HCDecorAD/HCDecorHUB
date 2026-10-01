# Live CP1 runbook

1. Run scripts\03_ENSURE_MANAGED_EDGE.bat.
2. In that managed Edge profile, sign in to ChatGPT manually if needed.
3. Open at least two existing conversations so both URLs contain /c/<conversation_id>.
4. Run scripts\11_CAPTURE_CP1_EVIDENCE.bat.
5. Inspect logs\cp1-real-cdp.json. It must contain at least two distinct non-empty conversation_id values.
6. Launch HC_AutoChat.bat and bind distinct aliases.
7. Close/restart managed Edge, reopen the same conversations, Refresh.
8. Aliases must resolve by conversation_id even if target_id changed.

Do not mark CP1_LIVE_MULTI_CHAT or CP1_ALIAS_RESTART PASS from unit tests alone.
