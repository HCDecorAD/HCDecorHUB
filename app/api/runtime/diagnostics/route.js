import {runtimeDiagnostics} from "../../../../lib/runtime-diagnostics";
export async function GET(){return Response.json(runtimeDiagnostics())}
