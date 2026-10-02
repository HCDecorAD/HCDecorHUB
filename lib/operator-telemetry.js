import lifecycle from "../config/lifecycle-registry.json" with { type: "json" };
import {listMasterRuns,masterRunStoreInfo} from "./run-store";
import {durableRuntimeStatus} from "./durable-runtime";

export async function operatorTelemetrySnapshot({limit=12}={}){
 const runs=await listMasterRuns(limit);
 const runStore=masterRunStoreInfo();
 const durable=durableRuntimeStatus();
 const blockers=lifecycle.missions.filter(m=>["BLOCKED","WAITING_RESOURCE","WAITING_DEPENDENCY","HARD_BLOCKED","OWNER_REQUIRED"].includes(m.state));
 return {
  checked_at:new Date().toISOString(),
  production_write:false,
  sources:{
   lifecycle:{authority:"registry-baseline",production_authority:false},
   run_history:{authority:runStore.mode,production_authority:runStore.productionAuthority===true},
   durable_runtime:{authority:durable.productionAuthority===true?"production-provider":"runtime-diagnostic",production_authority:durable.productionAuthority===true}
  },
  durable_runtime:durable,
  recent_runs:runs,
  blockers,
  missions:lifecycle.missions
 };
}
