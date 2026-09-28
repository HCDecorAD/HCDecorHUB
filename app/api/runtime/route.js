import {runtimeCapabilities} from "../../../lib/data/store";
import {getHubConfig} from "../../../lib/hub-config";
import {productionGuard} from "../../../lib/hub-policy";
export async function GET(){const capabilities=runtimeCapabilities();const {agents}=getHubConfig();const guard=productionGuard();return Response.json({ok:true,architecture:agents.architecture,masterAgent:agents.master_agent.id,capabilities,policy:{fabricatedRuntimeState:false,productionGate:true,guardedActions:guard.guarded,restrictedActions:guard.restricted},routes:{website:"/",hub:"/hub",admin:"/admin",workspaces:"/hub/workspaces",agents:"/hub/agents",review:"/hub/review"}})}
