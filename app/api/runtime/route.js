import {runtimeCapabilities} from "../../../lib/data/store";
import {getHubConfig} from "../../../lib/hub-config";
import {productionGuard} from "../../../lib/hub-policy";
export async function GET(){
 const capabilities=runtimeCapabilities();
 const {agents}=getHubConfig();
 const guard=productionGuard();
 const durableExecution=process.env.HC_DURABLE_EXECUTION_ENABLED==="true";
 return Response.json({
  service:"ok",architecture:agents.architecture,masterAgent:agents.master_agent.id,
  masterRuntime:{
   planner:true,readExecute:true,parallelRead:{enabled:true,maxTasks:8},liveProbe:true,auditTrace:true,
   runHistory:"local-spool-non-authoritative",approvalQueue:"local-spool-non-authoritative",
   durableExecution,preflight:true,previewWorkflow:["content","media","publishing"],
   writeExecute:durableExecution?"durable-provider-policy-gated":"internal-draft-or-preview-only",
   productionWrite:false
  },
  capabilities,
  capabilityContract:{
   booleanOnly:true,secretsExposed:false,readRuntimeConfigured:capabilities.cmsRead===true,
   durableWriteRuntimeConfigured:["cmsWrite","driveWrite","leadWrite","projectWrite"].some(k=>capabilities[k]===true),
   durableExecutionEnabled:durableExecution,localSpoolAuthority:false,productionWrite:false
  },
  policy:{fabricatedRuntimeState:false,productionGate:guard.guarded.length>0,guardedActions:guard.guarded,restrictedActions:guard.restricted},
  routes:{website:"/",hub:"/hub",admin:"/admin",workspaces:"/hub/workspaces",agents:"/hub/agents",review:"/hub/review"},
  checkedAt:new Date().toISOString()
 })
}
