import {v9Readiness,finalGate} from "../../../../lib/v9-final-preflight.mjs";
export async function GET(){const readiness=v9Readiness();return Response.json({service:"ok",readiness,final:finalGate(readiness),production_write:false})}
