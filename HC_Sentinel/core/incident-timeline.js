export class IncidentTimeline{
  constructor(store){this.store=store;}
  async append(incidentId,event){
    if(!incidentId)throw new Error("INCIDENT_ID_REQUIRED");
    return this.store.update(x=>{x.items??={};x.items[incidentId]??=[];x.items[incidentId].push({at:new Date().toISOString(),...event});return x;},{items:{}});
  }
  async get(incidentId){return (await this.store.read({items:{}})).items?.[incidentId]??[];}
}
