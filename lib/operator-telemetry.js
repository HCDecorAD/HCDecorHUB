import lifecycle from "../config/lifecycle-registry.json";
import {listMasterRuns,masterRunStoreInfo} from "./run-store";
import {durableRuntimeStatus} from "./durable-runtime";
import {DurableWorkerHeartbeatStore} from "./governor/durable-worker-heartbeats.mjs";
import {DurableEvidenceStore} from "./governor/durable-evidence-store.mjs";
import path from "node:path";

export async function operatorTelemetrySnapshot({limit=12}={}){
 const runs=await listMasterRuns(limit);
 const runStore=masterRunStoreInfo();
 const durable=durableRuntimeStatus();
 const blockers=lifecycle.missions.filter(m=>["BLOCKED","WAITING_RESOURCE","WAITING_DEPENDENCY","HARD_BLOCKED","OWNER_REQUIRED"].includes(m.state));
 const heartbeatStore=new DurableWorkerHeartbeatStore(path.join(process.cwd(),".runtime","worker-heartbeats.json"));
 const workers=heartbeatStore.list();
 const suspect_workers=workers.filter(w=>w.health==="SUSPECT");
 const evidenceStore=new DurableEvidenceStore(path.join(process.cwd(),".runtime","evidence-events.jsonl"));
 const recent_evidence=evidenceStore.list({limit});
 const worker_evidence=workers.map(w=>({...w,evidence:evidenceStore.list({mission_id:w.mission_id,limit:5})}));
 return {
  checked_at:new Date().toISOString(),
  production_write:false,
  sources:{
   lifecycle:{authority:"registry-baseline",production_authority:false},
   run_history:{authority:runStore.mode,production_authority:runStore.productionAuthority===true},
   durable_runtime:{authority:durable.productionAuthority===true?"production-provider":"runtime-diagnostic",production_authority:durable.productionAuthority===true},
   worker_heartbeats:{authority:"local-spool",production_authority:false},
   evidence_events:{authority:"local-spool",production_authority:false}
  },
  durable_runtime:durable,
  recent_runs:runs,
  blockers,
  workers:worker_evidence,
  suspect_workers,
  recent_evidence,
  missions:lifecycle.missions
 };
}
