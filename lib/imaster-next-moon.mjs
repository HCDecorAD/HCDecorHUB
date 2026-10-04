import {scheduleResourceAware} from './governor/resource-scheduler.mjs';

export function buildGlobalStateGraph({catalog,lifecycle,capabilities=[]}){
  const nodes=[]; const edges=[]; const add=(type,id,data={})=>nodes.push({type,id,...data});
  for(const p of catalog.projects||[]) add('project',p.project_id,{state:p.state});
  for(const s of catalog.systems||[]) add('system',s.system_id,{state:s.state});
  for(const t of catalog.tools||[]) add('tool',t.tool_id,{risk_class:t.risk_class});
  for(const c of capabilities||[]) add('capability',c.capability_id,{provider_tool:c.provider_tool});
  for(const g of lifecycle.goals||[]){add('goal',g.goal_id,{state:g.state});edges.push({from:g.goal_id,to:g.project_id,rel:'belongs_to'});for(const s of g.system_refs||[])edges.push({from:g.goal_id,to:s,rel:'uses_system'});}
  for(const m of lifecycle.missions||[]){add('mission',m.mission_id,{state:m.state,checkpoint:m.checkpoint});edges.push({from:m.mission_id,to:m.goal_id,rel:'serves_goal'});for(const t of m.tool_refs||[])edges.push({from:m.mission_id,to:t,rel:'uses_tool'});for(const e of m.evidence_refs||[])add('evidence',m.mission_id+':'+e,{ref:e});}
  return {schema_version:'1.0.0',nodes,edges,summary:{projects:(catalog.projects||[]).length,systems:(catalog.systems||[]).length,tools:(catalog.tools||[]).length,goals:(lifecycle.goals||[]).length,missions:(lifecycle.missions||[]).length,capabilities:capabilities.length}};
}

export function planContinuousWork({tasks,workers,resources,budget_ledger=null,completed_task_ids=[]}){
  const plan=scheduleResourceAware({tasks,workers,resources,budget_ledger,completed_task_ids});
  return {...plan,law:'READY_WORK + SAFE_CAPACITY -> EXECUTE',company_waiting:plan.dispatches.length===0&&plan.waiting_dependency.length+plan.waiting_resource.length>0};
}

export function workerFactoryPlan({dispatches=[],workers=[]}){
  const byId=new Map(workers.map(w=>[w.worker_id,w]));
  return dispatches.map(d=>({worker_id:d.worker_id,task_id:d.task_id,mode:'LEASED',concurrency_limit:byId.get(d.worker_id)?.concurrency_limit||1,crash_policy:'LEASE_EXPIRE_REQUEUE',fencing:'REQUIRED'}));
}

export function nextMoonCycle(input){
  const graph=buildGlobalStateGraph(input);
  const planner=planContinuousWork(input);
  const leases=workerFactoryPlan({dispatches:planner.dispatches,workers:input.workers});
  return {graph,planner,leases,operator_surfaces:['chat','mobile','dashboard','local'],mission_owner:'iMaster durable control plane'};
}
