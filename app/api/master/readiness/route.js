import {operationalSummary} from "../../../../lib/operational-summary";
import {durableRuntimeStatus} from "../../../../lib/durable-runtime";
export async function GET(request){
 const live=new URL(request.url).searchParams.get("live")==="1";
 const summary=await operationalSummary({live});
 const durable=durableRuntimeStatus();
 const durableExecution=durable.available;
 const durableExecutionReason=durable.reason;
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
