import {getHubConfig} from "../../../../lib/hub-config";
import {productionGuard} from "../../../../lib/hub-policy";
export async function GET(){const {integrations}=getHubConfig();const m=integrations.integrations.metricool;const guard=productionGuard();return Response.json({service:"ok",storage:"external-integration",localQueue:false,provider:"metricool",configState:m.state||"unknown",liveHealth:"not_checked",brandId:m.brand_id||null,timezone:m.timezone||null,policy:m.publish_policy||null,productionGate:guard.guarded.includes("publish")})}
export async function POST(){return Response.json({ok:false,error:"Local publishing queue is retired. Use the configured publishing integration after Review Center approval."},{status:410})}
export async function PATCH(){return Response.json({ok:false,error:"Local publishing queue is retired."},{status:410})}
export async function DELETE(){return Response.json({ok:false,error:"No local publishing queue exists."},{status:410})}
