import {runtimeDiagnostics} from "../../../../lib/runtime-diagnostics";
import {crmRuntime} from "../../../../lib/crm/config";
import {probeGoogleRuntime} from "../../../../lib/crm/google";
export async function GET(request){const x=runtimeDiagnostics(),live=new URL(request.url).searchParams.get("live")==="1",r=crmRuntime();if(!live||!r.writeEnabled)return Response.json(x);const probe=await probeGoogleRuntime();return Response.json({...x,credential_probe:{google:{ok:probe.ok,sheets:probe.sheets,drive:probe.drive}},credentials_validated:probe.credentials_validated===true,production_write:false})}
