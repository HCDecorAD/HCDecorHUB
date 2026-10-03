export class FindingStore{
  constructor(store){this.store=store;}
  async upsert(finding){
    if(!finding?.id) throw new Error("FINDING_ID_REQUIRED");
    return this.store.update(x=>{
      x.items??=[];
      const i=x.items.findIndex(v=>v.id===finding.id);
      const next={updatedAt:new Date().toISOString(),...finding};
      if(i>=0)x.items[i]={...x.items[i],...next};else x.items.push(next);
      return x;
    },{items:[]});
  }
  async list({state,projectId}={}){
    const items=(await this.store.read({items:[]})).items??[];
    return items.filter(x=>(!state||x.state===state)&&(!projectId||x.projectId===projectId));
  }
}
