import {operationalSummary} from "../../../../lib/operational-summary";
export async function GET(request){
 const live=new URL(request.url).searchParams.get("live")==="1";
 const summary=await operationalSummary({live});
 const durableExecution=process.env.HC_DURABLE_EXECUTION_ENABLED==="true";
 return Response.json({
  ok:true,
  ...summary,
  production_readiness:{
   durable_execution:durableExecution,
   local_spool_authority:false,
   mutation_execution_enabled:durableExecution,
   production_write:"approval-required"
  }
 });
}
