import {productionGuard} from "../../../../lib/hub-policy";
import {getHubConfig} from "../../../../lib/hub-config";
export async function GET(){const guard=productionGuard();const {integrations}=getHubConfig();const m=integrations.integrations.metricool;return Response.json({ok:true,pipeline:["source","adapt","review","schedule","publish","audit"],provider:"metricool",providerState:m.state||"unknown",brandId:m.brand_id||null,timezone:m.timezone||null,productionGate:guard.guarded.includes("publish"),localQueue:false,durableSource:"external-integration",policy:m.publish_policy||null})}
