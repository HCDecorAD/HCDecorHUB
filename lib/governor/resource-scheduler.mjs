const ACTIVE = new Set(['CLAIMED','RUNNING','VERIFYING']);

function asSet(v){return new Set(Array.isArray(v)?v:[])}
function resourceFits(need={}, free={}){return Object.entries(need).every(([k,v]) => Object.prototype.hasOwnProperty.call(free,k) && Number(v||0) <= Number(free[k]));}
function capabilityFits(task, worker){const have=asSet(worker.capabilities);return (task.required_capabilities||[]).every(c=>have.has(c));}
function workerFree(worker){const limit=Math.max(1, Number(worker.concurrency_limit||1)); const active=Number(worker.active_count||0); return active < limit;}
function depsDone(task, done){return (task.depends_on||[]).every(id=>done.has(id));}
function ageScore(task, now){const t=Date.parse(task.ready_since||''); if(!Number.isFinite(t)) return 0; return Math.max(0, Math.floor((now-t)/60000));}
function laneRank(lane){return lane==='critical'?3:lane==='recovery'?2:1;}

export function scheduleResourceAware({tasks=[],workers=[],resources={},completed_task_ids=[],budget_ledger=null,now=Date.now()}={}){
  const done=asSet(completed_task_ids);
  const free={...resources};
  const slots=workers.map(w=>({...w,active_count:Number(w.active_count||0)}));
  const result={dispatches:[],waiting_resource:[],waiting_dependency:[],waiting_capability:[],untouched:[],budget_decisions:{}};
  const ready=[];
  for(const task of tasks){
    if(task.state!=='READY'){result.untouched.push(task.task_id);continue;}
    if(!depsDone(task,done)){result.waiting_dependency.push(task.task_id);continue;}
    ready.push(task);
  }
  ready.sort((a,b)=>{
    const lane=laneRank(b.lane)-laneRank(a.lane); if(lane) return lane;
    const pri=Number(b.priority||0)-Number(a.priority||0); if(pri) return pri;
    const age=ageScore(b,now)-ageScore(a,now); if(age) return age;
    return String(a.task_id).localeCompare(String(b.task_id));
  });
  for(const task of ready){
    const candidates=slots.filter(w=>workerFree(w)&&capabilityFits(task,w));
    if(!candidates.length){result.waiting_capability.push(task.task_id);continue;}
    if(!resourceFits(task.resources||{},free)){result.waiting_resource.push(task.task_id);continue;}
    let budgetOk=true;
    for(const [budgetId,costRaw] of Object.entries(task.budgets||{})){
      if(!budget_ledger){budgetOk=false;result.budget_decisions[task.task_id]={allowed:false,budget_id:budgetId,reason:'BUDGET_LEDGER_REQUIRED'};break;}
      try{
        const decision=budget_ledger.take(budgetId,Number(costRaw||0),{decision_id:task.decision_id||task.task_id});
        result.budget_decisions[task.task_id]=decision;
        if(!decision.allowed){budgetOk=false;break;}
      }catch(err){
        budgetOk=false;
        result.budget_decisions[task.task_id]={allowed:false,budget_id:budgetId,reason:String(err?.message||err)};
        break;
      }
    }
    if(!budgetOk){result.waiting_resource.push(task.task_id);continue;}
    candidates.sort((a,b)=>Number(a.active_count||0)-Number(b.active_count||0)||String(a.worker_id).localeCompare(String(b.worker_id)));
    const worker=candidates[0];
    worker.active_count++;
    for(const [k,v] of Object.entries(task.resources||{})) free[k]=Number(free[k]??0)-Number(v||0);
    result.dispatches.push({task_id:task.task_id,worker_id:worker.worker_id,state:'CLAIMED',lane:task.lane||'normal'});
  }
  result.remaining_resources=free;
  result.worker_loads=Object.fromEntries(slots.map(w=>[w.worker_id,w.active_count]));
  return result;
}

export const GOVERNOR_STATES=['PLANNED','READY','CLAIMED','RUNNING','WAITING_DEPENDENCY','WAITING_RESOURCE','BLOCKED','VERIFYING','DONE','OWNER_REQUIRED','SAFETY_STOP','HARD_BLOCKED'];
export function isTerminalState(state){return ['DONE','OWNER_REQUIRED','SAFETY_STOP','HARD_BLOCKED'].includes(state)}
export function isActiveState(state){return ACTIVE.has(state)}
