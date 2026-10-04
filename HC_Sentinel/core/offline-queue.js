export class OfflineQueue{
  constructor(store){this.store=store;}
  async enqueue(item){return this.store.update(x=>{x.items??=[];x.items.push({id:item.id??crypto.randomUUID(),attempts:0,...item});return x;},{items:[]});}
  async drain(handler,{limit=50}={}){
    const state=await this.store.read({items:[]});const keep=[],done=[];
    for(const item of (state.items??[]).slice(0,limit)){
      try{await handler(item);done.push(item.id);}
      catch{keep.push({...item,attempts:(item.attempts??0)+1});}
    }
    keep.push(...(state.items??[]).slice(limit));
    await this.store.write({items:keep});
    return {done,remaining:keep.length};
  }
}
