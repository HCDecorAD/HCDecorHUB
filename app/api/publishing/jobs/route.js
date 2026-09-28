import {getHubConfig} from "../../../../lib/hub-config";
export async function GET(){const {integrations}=getHubConfig();const m=integrations.integrations.metricool;return Response.json({ok:true,storage:"external-integration",volatileQueue:false,provider:"metricool",providerState:m.state||"unknown",brandId:m.brand_id||null,timezone:m.timezone||null,policy:m.publish_policy||null,productionGate:true})}
export async function POST(){return Response.json({ok:false,error:"Local publishing queue is retired. Use the configured publishing integration after Review Center approval."},{status:410})}
export async function PATCH(){return Response.json({ok:false,error:"Local publishing queue is retired."},{status:410})}
export async function DELETE(){return Response.json({ok:false,error:"No local publishing queue exists."},{status:410})}
