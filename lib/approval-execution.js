import {durableExecutionAvailable} from "./durable-runtime";

/**
 * Legacy compatibility guard.
 * Local approval spool is never production execution authority.
 */
export async function requireApprovedExecution(){
 if(!durableExecutionAvailable())return {ok:false,status:503,error:"authenticated_durable_executor_required",production_authority:false};
 return {ok:false,status:503,error:"durable_approval_lookup_not_implemented",production_authority:false};
}
