export class Watchdog{
  constructor({probe,start,stop,now=()=>Date.now(),cooldownMs=30000}){
    this.probe=probe;this.start=start;this.stop=stop;this.now=now;this.cooldownMs=cooldownMs;this.lastRestartAt=0;
  }
  async tick(){
    const health=await this.probe();
    if(health?.ok)return {status:"HEALTHY",health};
    const now=this.now();
    if(now-this.lastRestartAt<this.cooldownMs)return {status:"COOLDOWN",health};
    try{await this.stop?.();}catch{}
    await this.start();
    this.lastRestartAt=now;
    const after=await this.probe();
    return {status:after?.ok?"RECOVERED":"FAILED_RECOVERY",health:after};
  }
}
