export class TargetStore{
  constructor(store,{defaults=[]}={}){this.store=store;this.defaults=defaults;}
  async list(){
    const live=(await this.store.read({targets:[]})).targets??[];
    const map=new Map(this.defaults.map(x=>[x.id,x]));
    for(const x of live)map.set(x.id,x);
    return [...map.values()];
  }
  async add(target){
    if(!target?.id||!target?.projectId||!target?.url)throw new Error("TARGET_FIELDS_REQUIRED");
    let u;try{u=new URL(target.url);}catch{throw new Error("TARGET_URL_INVALID");}
    if(!["http:","https:"].includes(u.protocol))throw new Error("TARGET_URL_INVALID");
    const clean={enabled:true,viewports:{desktop:{width:1440,height:900},mobile:{width:390,height:844}},...target,url:u.toString()};
    await this.store.update(x=>{x.targets??=[];const i=x.targets.findIndex(v=>v.id===clean.id);if(i>=0)x.targets[i]=clean;else x.targets.push(clean);return x;},{targets:[]});
    return clean;
  }
}
