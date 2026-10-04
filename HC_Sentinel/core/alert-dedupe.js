export class AlertDedupe{
  constructor({windowMs=300000,now=()=>Date.now()}={}){this.windowMs=windowMs;this.now=now;this.last=new Map();}
  accept(key){
    const now=this.now(),prev=this.last.get(key);
    if(prev!==undefined&&now-prev<this.windowMs)return false;
    this.last.set(key,now);return true;
  }
}
