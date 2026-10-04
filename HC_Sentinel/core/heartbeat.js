export class Heartbeat{
  constructor({ttlMs=120000,now=()=>Date.now()}={}){this.ttlMs=ttlMs;this.now=now;this.last=new Map();}
  beat(id,meta={}){const at=this.now();const x={id,at,...meta};this.last.set(id,x);return x;}
  status(id){const x=this.last.get(id);if(!x)return {id,state:"UNKNOWN"};const age=this.now()-x.at;return {id,state:age<=this.ttlMs?"HEALTHY":"STALE",ageMs:age,...x};}
  all(){return [...this.last.keys()].map(id=>this.status(id));}
}
