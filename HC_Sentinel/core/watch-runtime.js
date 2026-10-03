export class WatchRuntime{
  constructor({runTarget,notifications=null}={}){this.runTarget=runTarget;this.notifications=notifications;this.targets=new Map();}
  register(target,{intervalMs=3600000,enabled=true}={}){this.targets.set(target.id,{target,intervalMs,enabled,lastRunAt:0});return this.targets.get(target.id);}
  due(now=Date.now()){return [...this.targets.values()].filter(x=>x.enabled&&(now-x.lastRunAt>=x.intervalMs));}
  async tick(now=Date.now()){
    const results=[];
    for(const entry of this.due(now)){
      const result=await this.runTarget(entry.target);
      entry.lastRunAt=now;
      results.push({targetId:entry.target.id,result});
      if(result?.findings?.length) this.notifications?.publish({level:"warning",type:"WATCH_FINDING",targetId:entry.target.id,count:result.findings.length});
    }
    return results;
  }
}
