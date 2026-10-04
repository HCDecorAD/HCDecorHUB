export class AuditLedger{
  constructor(store){this.store=store;}
  async record(entry){
    if(!entry?.action)throw new Error("AUDIT_ACTION_REQUIRED");
    const safe={at:new Date().toISOString(),actor:entry.actor??"system",action:entry.action,target:entry.target??null,result:entry.result??null};
    return this.store.update(x=>{x.items??=[];x.items.push(safe);return x;},{items:[]});
  }
  async list(){return (await this.store.read({items:[]})).items??[];}
}
