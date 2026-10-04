import {aiConnectionStatus,probeAIConnections} from "../../../../lib/ai/connections";
export async function GET(request){const live=new URL(request.url).searchParams.get("live")==="1";const status=aiConnectionStatus();const probes=live?await probeAIConnections():null;return Response.json({service:"ok",...status,liveHealth:live?probes:"not_checked",checkedAt:new Date().toISOString()})}
