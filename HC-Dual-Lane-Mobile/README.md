# HC Dual-Lane Mobile

Status: BOOTSTRAP
Architecture: Mobile-first, Direct Mobile primary; HOCUONG/PC worker secondary; Mesh fallback.

## Routing
1. Mobile-only job -> HC Mobile Agent (DIRECT)
2. PC-heavy job -> HOCUONG/HCDR
3. Mobile <-> PC -> local fast bridge first
4. Internet/Mesh -> fallback
5. Delete -> Trash -> Verify; permanent delete guarded
6. No root; no fake PASS

## Bootstrap phases
P0 Source map / licensing
P1 Android Agent skeleton
P2 MediaStore + Storage permissions
P3 Shared Command Engine
P4 Mobile-PC bridge
P5 iMaster Router
P6 Real-device E2E
