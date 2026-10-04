export class DesktopWatchStore{
  constructor(store){this.store=store;}
  async list(){return (await this.store.read({items:[]})).items??[];}
  async upsert(watch){
    if(!watch?.id)throw new Error("WATCH_ID_REQUIRED");
    const clean={
      enabled:false,sourceType:"WINDOW",intervalSec:60,stuckSec:300,cooldownSec:600,
      autoSend:false,command:"iMaster next",stopOnDone:false,donePatterns:["DONE"],
      ...watch,updatedAt:new Date().toISOString()
    };
    return this.store.update(x=>{x.items??=[];const i=x.items.findIndex(v=>v.id===clean.id);if(i>=0)x.items[i]={...x.items[i],...clean};else x.items.push(clean);return x;},{items:[]});
  }
  async patch(id,patch){
    let found=false;
    const state=await this.store.update(x=>{x.items??=[];const i=x.items.findIndex(v=>v.id===id);if(i<0)return x;found=true;x.items[i]={...x.items[i],...patch,updatedAt:new Date().toISOString()};return x;},{items:[]});
    if(!found)throw new Error("WATCH_NOT_FOUND");return state.items.find(v=>v.id===id);
  }
  async remove(id){return this.store.update(x=>{x.items=(x.items??[]).filter(v=>v.id!==id);return x;},{items:[]});}
}
