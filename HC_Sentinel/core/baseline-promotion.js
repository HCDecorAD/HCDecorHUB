export const BaselineState=Object.freeze({CANDIDATE:"CANDIDATE",REVIEW:"REVIEW",APPROVED:"APPROVED",REJECTED:"REJECTED"});
export class BaselinePromotion{
  constructor(){this.items=new Map();}
  propose({id,projectId,viewport,revision,evidence}){
    if(!id||!revision) throw new Error("BASELINE_ID_REVISION_REQUIRED");
    const x={id,projectId,viewport,revision,evidence,state:BaselineState.CANDIDATE,history:["CANDIDATE"]};
    this.items.set(id,x);return x;
  }
  review(id){const x=this.items.get(id);if(!x)throw new Error("BASELINE_NOT_FOUND");x.state=BaselineState.REVIEW;x.history.push("REVIEW");return x;}
  approve(id,{reviewer}={}){const x=this.items.get(id);if(!x||x.state!==BaselineState.REVIEW) return false;x.state=BaselineState.APPROVED;x.reviewer=reviewer??"operator";x.history.push("APPROVED");return true;}
  reject(id){const x=this.items.get(id);if(!x)return false;x.state=BaselineState.REJECTED;x.history.push("REJECTED");return true;}
}
