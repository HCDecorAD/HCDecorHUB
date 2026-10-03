export class LogStore{
  constructor(store){this.store=store;}
  async append(entry){
    return this.store.update(x=>{
      x.items??=[];
      x.items.push({at:new Date().toISOString(),level:"info",...entry});
      if(x.items.length>1000)x.items=x.items.slice(-1000);
      return x;
    },{items:[]});
  }
  async list({level,limit=100}={}){
    let items=(await this.store.read({items:[]})).items??[];
    if(level)items=items.filter(x=>x.level===level);
    return items.slice(-Math.max(1,Math.min(Number(limit)||100,500)));
  }
}
