# iMaster Dual Lane

Production routing policy for HOCUONG.

## Route

`ChatGPT -> Dual Lane Controller -> MCP-first -> HOCUONG`

Fallback for a write that MCP cannot complete:

`ChatGPT -> Dual Lane Controller -> RDC-on-demand -> HOCUONG -> MCP verify`

## Hard rules

1. MCP is always the default lane.
2. RDC is on-demand only for WRITE/GUI operations that MCP cannot complete.
3. Never write to the same target concurrently from MCP and RDC.
4. Acquire a target lock before fallback; release it after verification.
5. After RDC write, verify through MCP when MCP can observe the target.
6. A failed MCP write may fall back once; do not loop between lanes.
7. Never expose credentials or raw unauthenticated shell through the public controller.
8. DONE requires one real targeted runtime test.

## State machine

`READY -> MCP -> PASS`

`MCP_WRITE_FAIL -> LOCK -> RDC_WRITE -> MCP_VERIFY -> PASS -> UNLOCK`

If verification is unavailable: `RDC_WRITE -> TARGET_EVIDENCE -> PASS -> UNLOCK`.

## Status

- MCP read/control: primary
- RDC: rescue/write fallback
- HCDR Local: local executor/build/repair lane; independent of interactive RDC fallback
