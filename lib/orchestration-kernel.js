import {buildTaskDag} from "./task-dag";
import {evaluatePolicy,isMutation} from "./policy-engine";
export function prepareRun(input={},principal={}){
 const dag=buildTaskDag(input); if(!dag.ok)return dag;
 const decisions=dag.nodes.map(n=>({task_id:n.task_id,...evaluatePolicy({role:principal.role||"viewer",action:n.plan.task.action,workspace_id:dag.workspace_id,grants:principal.grants||[],approved:Boolean(principal.approved)})}));
 const denied=decisions.filter(x=>!x.allowed);
 return {ok:denied.length===0,status:denied.length?403:200,run_state:denied.length?"blocked":"ready",dag,policy:decisions,execution:{parallel_reads:true,mutation_serialized:true,fail_closed:true},production_write:dag.nodes.some(n=>isMutation(n.plan.task.action)),audit_required:true};
}
