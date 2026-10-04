export function aggregateRuntimeHealth({heartbeat,workers=[],targets=[],findings=[]}){
  const stale=heartbeat?.filter?.(x=>x.state==="STALE")??[];
  const badWorkers=workers.filter(x=>x.state&&x.state!=="HEALTHY");
  const badTargets=targets.filter(x=>x.ok===false);
  const critical=findings.filter(x=>x.severity==="critical"&&x.state!=="RESOLVED");
  let state="HEALTHY";
  if(critical.length||badTargets.length)state="DEGRADED";
  if(stale.length)state="STALE";
  return {state,staleHeartbeat:stale.length,badWorkers:badWorkers.length,badTargets:badTargets.length,criticalOpen:critical.length};
}
