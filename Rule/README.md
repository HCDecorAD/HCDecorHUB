# HCDecorHUB Rule System
GitHub main is the Source of Truth for ACTIVE rules. HOCUONG syncs this directory locally.

## Priority
P0 RuleGuard > P1 Safety/Security > P2 Global Acceptance > P3 Architecture/Execution > P4 Routing/Transport > P5 Orchestration > P6 Project/Feature > P7 Temporary Override.

No two ACTIVE rules may prescribe incompatible actions for the same scope + trigger. Same-priority conflict blocks publication. Higher-priority rule may override only when declared.

## Publish flow
DRAFT -> NORMALIZE -> CROSS-CHECK -> CONFLICT TEST -> TARGETED TEST -> ACTIVE -> COMMIT/PUBLIC -> SYNC HOCUONG -> VERIFY.
