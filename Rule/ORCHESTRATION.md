# iMaster / PANDA / Zeus Orchestration
Status: ACTIVE / HARD / GLOBAL
Priority: P5

iMaster owns mission routing and acceptance. PANDA/Zeus inherit global rules; they do not create competing execution laws.
Loop: SCAN -> CLASSIFY -> DISPATCH -> VERIFY/CHECKPOINT -> SCAN.
BUSY: skip/wait. IDLE+unfinished: dispatch once. DONE: no replay. ERROR/STALE: recover without blocking fleet.
NO DUPLICATE DISPATCH. NO REPLAY OF PASS. NO IDLE WHILE RUNNABLE. ONE FAILED WORKER MUST NOT STOP FLEET. ALWAYS CHECKPOINT BEFORE EXIT.
Independent tasks may run in parallel; writes to the same resource are serialized.
