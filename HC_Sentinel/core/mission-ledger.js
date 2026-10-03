export class MissionLedger{
  constructor(store){this.store=store;}
  async append(event){return this.store.update(x=>{x.events??=[];x.events.push({at:new Date().toISOString(),...event});return x;},{events:[]});}
  async list(){return (await this.store.read({events:[]})).events??[];}
}
