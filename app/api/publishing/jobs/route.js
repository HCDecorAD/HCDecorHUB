import {getHubConfig} from "../../../../lib/hub-config";import {productionGuard} from "../../../../lib/hub-policy";import {requireSameOriginMutation} from "../../../../lib/request-guard";
export async function GET(){const {integrations}=getHubConfig();const m=integrations.integrations.metricool;const guard=productionGuard();return Response.json({service:"ok",storage:"external-integration",localQueue:false,provider:"metricool",configState:m.config_state||"unknown",liveHealth:"not_checked",brandId:m.brand_id||null,timezone:m.timezone||null,policy:m.publish_policy||null,productionGate:guard.guarded.includes("publish")})}
function retired(request,message){const guard=requireSameOriginMutation(request);if(guard)return guard;return Response.json({ok:false,error:message},{status:410})}
export async function POST(request){return retired(request,"Local publishing queue is retired. Use the configured publishing integration after Review Center approval.")}
export async function PATCH(request){return retired(request,"Local publishing queue is retired.")}
export async function DELETE(request){return retired(request,"No local publishing queue exists.")}
