import {operationalSummary} from "../../../../lib/operational-summary";export async function GET(){return Response.json({ok:true,...await operationalSummary()})}
