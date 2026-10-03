import { TriggerBroker } from "../core/scheduler.js";
import { LeaseRegistry } from "../core/lease.js";
import { WorkerRegistry, WorkerState } from "../core/worker-registry.js";
import { ControlPlane } from "../core/control-plane.js";
import { createMission, TriggerMode } from "../core/mission.js";
import { mkdir, writeFile } from "node:fs/promises";

let now=0;
const broker=new TriggerBroker();
broker.push({mode:"SCHEDULED"});broker.push({mode:"MANUAL_NOW"});
const first=broker.next();

const leases=new LeaseRegistry({now:()=>now});
const a=leases.claim("job","A",10); now=11; const b=leases.claim("job","B",10);

const workers=new WorkerRegistry();
workers.upsert({id:"visual-A",capabilities:["visual"]});
workers.upsert({id:"catalog-B",capabilities:["catalog"]});
workers.setState("visual-A",WorkerState.OFFLINE);
const unrelated=workers.route("catalog");

const cp=new ControlPlane();
cp.submit(createMission({id:"golden",mode:TriggerMode.COMMAND}));
const effect1=cp.claimEffect("golden","effect:deploy:test");
const effect2=cp.claimEffect("golden","effect:deploy:test");

const result={
  manualPreempts:first?.mode==="MANUAL_NOW",
  staleLeaseTakeover:a===true&&b===true,
  unrelatedLaneContinues:unrelated?.id==="catalog-B",
  duplicateEffectBlocked:effect1===true&&effect2===false
};
await mkdir(new URL("../evidence/p10/",import.meta.url),{recursive:true});
await writeFile(new URL("../evidence/p10/golden.json",import.meta.url),JSON.stringify(result,null,2));
console.log(JSON.stringify(result));
if(Object.values(result).some(v=>v!==true)) process.exit(2);
