# HC Dual-Lane Mobile - P1 to P10

- P1 Android Agent Skeleton + APK build
- P2 Permissions + MediaStore + SAF
- P3 File/Media Commands: list/search/copy/move/rename/create
- P4 Trash Guard + restore/verify + audit
- P5 Device Registry + capability discovery + ECO/ACTIVE/TURBO
- P6 Secure Command Transport + pairing/auth + job queue/resume
- P7 Mobile-PC Fast Bridge + Windows/Phone Link adapter
- P8 HOCUONG adapter: HCDR + ADB + scrcpy; heavy-job routing
- P9 iMaster Router + shared command schema + fallback policy
- P10 Real-device E2E + package/release

PASS rule: ANALYZE -> BUILD -> RUN -> REAL TEST -> FIX -> RETEST -> PASS.
No fake PASS. Mutation must verify. Delete defaults to Trash.
Parallel lanes allowed when dependencies do not conflict.
