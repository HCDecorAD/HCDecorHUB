# Zeus 24/7 Local Control Plane

iMaster authority. Zeus inherits HC_BOOTSTRAP_V2_LOCAL_FIRST.

Runtime: LOCAL DIRECT -> Zeus local daemon -> Zeus Bridge/Extension (127.0.0.1:8766) -> HCDR recovery -> GitHub evidence/CI -> RDC rescue.

Loop: SCAN -> RECONCILE -> CLASSIFY -> DISPATCH -> CHECKPOINT -> JUMP -> SCAN.

Hard rules:
- NO IDLE WHILE RUNNABLE.
- NO DUPLICATE DISPATCH.
- NO REPLAY OF PASS.
- NO DONE WHILE HANDOFF EXISTS.
- ALWAYS CHECKPOINT BEFORE EXIT.
- ONE FAILED WORKER MUST NOT STOP THE FLEET.
- UNKNOWN/MISSING FROM EXTENSION REGISTRY DOES NOT MEAN CHAT DOES NOT EXIST.
- Never CREATE until reconciliation proves no matching existing chat/tab.

Reconcile Bridge /tabs + local browser/Extension discovery + persisted Zeus state. CID is primary identity; title is only a label.

Default acceptance fleet: Zeus 24/7; HCDecorHUB V10; Update PASS; Tiếp tục PANDA LIVE.

Worker keep-alive prompt:
[iMaster ZEUS247] Inherit HC_BOOTSTRAP_V2_LOCAL_FIRST. Continue from latest checkpoint. Never replay PASS. When current unit finishes, continue with next valid runnable action. If blocked, checkpoint and hand off without stopping the fleet.

PASS requires all four lanes reconciled, two complete scan-dispatch-scan cycles, BUSY skip, IDLE unfinished exact-once dispatch, no duplicate creation, restart-persistent checkpoint, and no HCDR use during healthy local cycles.
