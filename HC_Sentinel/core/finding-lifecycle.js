export const FindingState=Object.freeze({
  OPEN:"OPEN",ACKNOWLEDGED:"ACKNOWLEDGED",ROUTED:"ROUTED",VERIFYING:"VERIFYING",RESOLVED:"RESOLVED",NO_CHANGE:"NO_CHANGE",BLOCKED:"BLOCKED"
});
export class FindingLifecycle{
  constructor(){this.items=new Map();}
  create(f){if(!f?.id) throw new Error("FINDING_ID_REQUIRED");const x={state:FindingState.OPEN,history:[],...f};x.history.push({state:x.state});this.items.set(x.id,x);return x;}
  transition(id,state,meta={}){const x=this.items.get(id);if(!x) throw new Error("FINDING_NOT_FOUND");x.state=state;x.history.push({state,...meta});return x;}
  unresolved(){return [...this.items.values()].filter(x=>![FindingState.RESOLVED,FindingState.NO_CHANGE].includes(x.state));}
}
