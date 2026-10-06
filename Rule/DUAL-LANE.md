# Dual-Lane Routing
Status: ACTIVE / HARD / GLOBAL
Priority: P4

MCP-FIRST -> WRITE VIA AVAILABLE LANE -> VERIFY -> RETURN TO MCP.
- MCP is default for supported read/status/control/verification.
- For WRITE, use MCP mutation when healthy and supported; otherwise RDC/CDP/local safe lane is valid.
- MCP WRITE capability is NOT a DONE prerequisite.
- Lock target before WRITE. Never allow MCP/RDC/other lanes to write the same target concurrently.
- After fallback WRITE, verify through MCP when supported; otherwise use the strongest available independent verification.
- RDC is on-demand/rescue, not continuous observation.
