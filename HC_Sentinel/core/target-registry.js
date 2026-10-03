export class TargetRegistry {
  constructor(){ this.targets=new Map(); }
  register(target){
    if(!target?.id||!target?.url) throw new Error("TARGET_ID_URL_REQUIRED");
    const item={enabled:true,viewports:{desktop:{width:1440,height:900},mobile:{width:390,height:844}},...target};
    this.targets.set(item.id,item); return item;
  }
  get(id){return this.targets.get(id)??null;}
  all(){return [...this.targets.values()];}
  enabled(){return this.all().filter(t=>t.enabled);}
}
