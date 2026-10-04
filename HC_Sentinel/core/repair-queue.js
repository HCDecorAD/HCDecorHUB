export class RepairQueue{
  constructor(store){this.store=store;}
  async add(item){
    if(!item?.projectId||!item?.findingId)throw new Error("REPAIR_FIELDS_REQUIRED");
    const job={id:item.id??`repair-${Date.now()}`,createdAt:new Date().toISOString(),state:"ROUTED",...item};
    await this.store.update(x=>{x.items??=[];x.items.push(job);return x;},{items:[]});
    return job;
  }
  async list(){return (await this.store.read({items:[]})).items??[];}
}
