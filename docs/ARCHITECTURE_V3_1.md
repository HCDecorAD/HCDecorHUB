# HCDecor HUB Architecture v3.1

Locked topology: **One Core -> One Master Agent -> Multiple Workspaces -> Workers -> Tools/Adapters -> Verification/Audit**.

## Control plane
Context/Workspace Resolver -> Task DAG Planner -> Policy/Permission Gate -> Capability Router -> Worker/Adapter -> Verification -> Audit -> Result.

## Runtime contracts
- Task DAG rejects unknown dependencies and cycles; read-only stages may run in parallel; mutation stages are serialized/policy-gated.
- Capability Router is workspace-aware and resolves module -> capability -> worker; unknown workspace/module/capability fails closed.
- Deployment Adapter is provider-neutral. Provider changes are adapter/config changes, not Core changes.
- Policy Engine defaults to deny outside granted role/workspace and requires explicit approval for mutation/production actions.
- Identity model separates owner, workspace admin, store admin, staff and viewer. Commerce isolation uses store_id in addition to workspace_id.
- Release contract requires verification, audit and rollback capability before production mutation.

## Workspaces
HCDecor is the control workspace. GSC Senior and AMO Nguyen remain independent workspaces with their own repositories, domains, hosting adapters and policies.

## Phase boundary
Architecture v3.1 is the stable skeleton for the next phase. New capabilities should be added as workers/adapters/config contracts; Core topology changes require architecture review.
