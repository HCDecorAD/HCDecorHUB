import fs from "node:fs";
import path from "node:path";
import {getHubConfig} from "./hub-config";
const read=()=>JSON.parse(fs.readFileSync(path.join(process.cwd(),"config","capabilities.json"),"utf8").replace(/^\uFEFF/,""));
const MODULE_DEFAULTS={website:"website",content:"content",media:"media",publishing:"publishing",crm:"project",projects:"project",leads:"project",customers:"project",catalog:"project",inventory:"project",orders:"project",digital_twin:"website",hotspots:"website",reports:"report",audit:"report",agents:"qa"};
export function resolveCapability({workspace_id,module,capability,action="view"}={}){
 const registry=read(),{workspaces,agents}=getHubConfig();
 const workspace=workspaces.workspaces.find(w=>w.workspace_id===String(workspace_id||"").toLowerCase());
 if(!workspace)return {ok:false,error:"workspace_not_found"};
 if(!workspace.modules.includes(String(module||"")))return {ok:false,error:"module_not_available_in_workspace"};
 const defaults={...MODULE_DEFAULTS,...(registry.module_capability_defaults||{})};const defaultKey=defaults[module]||"qa";
 const requested=String(capability||"").toLowerCase();const actionKey=(action==="deploy"||action==="rollback")?"deployment":defaultKey;const key=requested||actionKey;
 if(requested&&requested!==defaultKey&&requested!==actionKey)return {ok:false,error:"capability_module_mismatch",module,capability:requested};
 const entry=registry.capabilities[key];
 if(!entry)return {ok:false,error:"capability_not_registered"};
 const worker=agents.workers.find(w=>w.id===entry.worker);
 if(!worker)return {ok:false,error:"capability_worker_not_registered"};
 if(!entry.modes.includes(action))return {ok:false,error:"capability_action_not_allowed",capability:key,action};
 return {ok:true,workspace_id:workspace.workspace_id,module,capability:key,worker:worker.id,action,production_write:entry.production_write,requires_approval:entry.production_write==="approval-required",routing_policy:registry.routing_policy};
}
