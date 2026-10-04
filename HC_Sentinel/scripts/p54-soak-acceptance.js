import {Heartbeat} from "../core/heartbeat.js";import {Watchdog} from "../runtime/watchdog.js";import {MaintenanceRunner} from "../core/maintenance-runner.js";import {aggregateRuntimeHealth} from "../core/runtime-health.js";
let now=0,running=true,restarts=0;
const hb=new Heartbeat({ttlMs:100,now:()=>now});hb.beat("runtime");
const wd=new Watchdog({probe:async()=>({ok:running}),start:async()=>{running=true;restarts++;hb.beat("runtime");},stop:async()=>{},now:()=>now,cooldownMs:50});
const maint=new MaintenanceRunner({jobs:[{id:"retention",run:async()=>({deleted:0})},{id:"backup-check",run:async()=>({ok:true})}]});
const cycles=[];
for(let i=0;i<24;i++){
  now+=25;if(i===8)running=false;
  const w=await wd.tick();if(running)hb.beat("runtime");
  const m=await maint.run();
  const health=aggregateRuntimeHealth({heartbeat:hb.all(),workers:[{state:"HEALTHY"}],targets:[{ok:true}],findings:[]});
  cycles.push({i,watchdog:w.status,maintenance:m.ok,health:health.state});
}
const result={cycles:cycles.length,restarts,allMaintenance:cycles.every(x=>x.maintenance),finalHealth:cycles.at(-1).health,recovered:cycles.some(x=>x.watchdog==="RECOVERED")};
console.log(JSON.stringify(result));
if(result.cycles!==24||result.restarts!==1||!result.allMaintenance||result.finalHealth!=="HEALTHY"||!result.recovered)process.exit(2);
