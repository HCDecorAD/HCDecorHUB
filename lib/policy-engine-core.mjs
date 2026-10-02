const MUT=new Set(['create','edit','delete','publish','manage','deploy','rollback']);

export function evaluatePolicyCore({permissions,durable_execution_available=false},{role='viewer',action='view',workspace_id,grants=[],approved=false,production=false}={}){
 const def=permissions.roles?.[role];
 if(!def)return {allowed:false,reason:'role_not_registered'};
 if(!def.actions.includes(action))return {allowed:false,reason:'action_not_granted'};
 if(def.scope!=='all'&&!grants.includes(workspace_id))return {allowed:false,reason:'workspace_not_granted'};
 if(MUT.has(action)&&!approved)return {allowed:false,reason:'approval_required',requires_approval:true};
 if(MUT.has(action)&&production&&!durable_execution_available)return {allowed:false,reason:'durable_execution_required',requires_durable_execution:true};
 return {allowed:true,reason:'granted',requires_approval:false};
}
export function isMutationCore(action){return MUT.has(action)}
