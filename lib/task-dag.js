import {randomUUID} from "node:crypto";
import {planMasterTask} from "./master-agent";

const MUTATIONS=new Set(["create","edit","delete","publish","manage","deploy","rollback"]);
export function buildTaskDag(input={}){
 const workspace_id=String(input.workspace_id||"").trim().toLowerCase();
 const tasks=Array.isArray(input.tasks)?input.tasks:[];
 if(!workspace_id||!tasks.length)return {ok:false,status:400,error:"workspace_and_tasks_required"};
 const ids=new Set(),nodes=[];
 for(const raw of tasks){
  const task_id=String(raw.task_id||randomUUID()).trim().slice(0,80);
  if(ids.has(task_id))return {ok:false,status:400,error:"duplicate_task_id"};
  ids.add(task_id);
  const planned=planMasterTask({...raw,workspace_id});
  if(!planned.ok)return {...planned,task_id};
  const depends_on=Array.isArray(raw.depends_on)?raw.depends_on.map(String):[];
  nodes.push({task_id,depends_on,plan:planned.plan});
 }
 for(const n of nodes)if(n.depends_on.some(id=>!ids.has(id)))return {ok:false,status:400,error:"unknown_dependency",task_id:n.task_id};
 const indegree=new Map(nodes.map(n=>[n.task_id,n.depends_on.length]));
 const dependents=new Map(nodes.map(n=>[n.task_id,[]]));
 for(const n of nodes)for(const d of n.depends_on)dependents.get(d).push(n.task_id);
 const groups=[],seen=new Set();
 let ready=nodes.filter(n=>indegree.get(n.task_id)===0).map(n=>n.task_id);
 while(ready.length){
  const group=[...ready];groups.push(group);ready=[];
  for(const id of group){seen.add(id);for(const child of dependents.get(id)){indegree.set(child,indegree.get(child)-1);if(indegree.get(child)===0)ready.push(child)}}
 }
 if(seen.size!==nodes.length)return {ok:false,status:400,error:"dependency_cycle"};
 const byId=new Map(nodes.map(n=>[n.task_id,n]));
 const stages=groups.map((ids,index)=>({stage:index+1,tasks:ids,parallel_safe:ids.every(id=>!MUTATIONS.has(byId.get(id).plan.task.action)),execution_policy:ids.every(id=>!MUTATIONS.has(byId.get(id).plan.task.action))?"parallel-read-safe":"sequential-or-policy-gated"}));
 return {ok:true,status:200,dag_id:randomUUID(),workspace_id,nodes,stages,requires_approval:nodes.some(n=>n.plan.execution.requires_approval),production_write:false,execution_started:false};
}
