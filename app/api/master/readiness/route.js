import {operationalSummary} from "../../../../lib/operational-summary";
export async function GET(request){
 const live=new URL(request.url).searchParams.get("live")==="1";
 const summary=await operationalSummary({live});
 const durableExecution=false;
 const durableExecutionReason="durable-provider-not-bound";
 return Response.json({
  ok:true,
  ...summary,
  production_readiness:{
   durable_execution:durableExecution,
   durable_execution_reason:durableExecutionReason,
   local_spool_authority:false,
   mutation_execution_enabled:false,
   production_write:"approval-required"
  }
 });
}
