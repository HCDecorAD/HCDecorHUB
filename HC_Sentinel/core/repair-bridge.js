export class RepairBridge {
  constructor({workers,timeline=null}={}){this.workers=workers;this.timeline=timeline;}
  dispatch(route,payload){
    if(route?.status==="REVIEW_REQUIRED") return {status:"REVIEW_REQUIRED"};
    const worker=this.workers.route(route.capability);
    if(!worker) return {status:"WAITING_CAPABILITY",capability:route.capability};
    const mission={workerId:worker.id,lane:route.lane,capability:route.capability,payload};
    this.timeline?.emit?.("REPAIR_DISPATCH",mission);
    return {status:"DISPATCHED",mission};
  }
}
