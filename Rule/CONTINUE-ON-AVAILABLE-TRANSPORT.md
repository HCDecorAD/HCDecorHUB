# CONTINUE-ON-AVAILABLE-TRANSPORT
Status: ACTIVE / HARD / GLOBAL
Priority: P1
Rule ID: CONTINUE_AVAILABLE_TRANSPORT
Version: 1.0
Scope: ROUTING / EXECUTION

## Trigger
A requested operation needs access to HOCUONG and the first/default remote transport is unavailable, offline, or lacks the required capability.

## Action
- DO NOT stop merely because the first transport is unavailable.
- Immediately check and use another already-authorized available transport/capability.
- Preferred runtime order for HOCUONG: iMaster Transport Mesh -> HCDR Local / Local Executor -> HOCUONG.
- RDC/Desktop Commander is fallback/rescue when Mesh/HCDR Local cannot perform the required operation.
- If a transport exposes only part of the required capability, continue through the next authorized lane for the missing capability.
- Report a blocker only after all currently available authorized lanes have been checked or when the required capability is genuinely not exposed.

## Forbidden
- Do not ask the user to manually reconnect RDC before checking Mesh/HCDR Local.
- Do not claim Desktop/process visibility unless the selected transport actually exposes it.
- Do not bypass authorization, security controls, or RuleGuard.

## Fallback
Mesh/HCDR Local unavailable or insufficient -> RDC/Desktop Commander -> GitHub relay/workflow if applicable -> explicit blocker with the exact missing capability.

## Conflict
RuleGuard P0 remains authoritative. This rule complements LOCAL_FIRST and DUAL_LANE; it does not override them.
