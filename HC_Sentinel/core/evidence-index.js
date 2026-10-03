export class EvidenceIndex{
  constructor(store){this.store=store;}
  async add(item){
    if(!item?.id) throw new Error("EVIDENCE_ID_REQUIRED");
    return this.store.update(x=>{x.items??=[];x.items.push({at:new Date().toISOString(),...item});return x;},{items:[]});
  }
  async list({projectId,type}={}){
    const items=(await this.store.read({items:[]})).items??[];
    return items.filter(x=>(!projectId||x.projectId===projectId)&&(!type||x.type===type));
  }
}
