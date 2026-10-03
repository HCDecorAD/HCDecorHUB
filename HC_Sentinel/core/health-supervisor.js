export class HealthSupervisor{
  evaluate({workers=[],targets=[],staleMissions=[]}){
    const unhealthyWorkers=workers.filter(x=>x.state!=="HEALTHY");
    const badTargets=targets.filter(x=>x.ok===false);
    const status=badTargets.length?"DEGRADED":unhealthyWorkers.length?"DEGRADED":"HEALTHY";
    return {status,unhealthyWorkers,badTargets,staleMissions,recoveryRequired:badTargets.length>0||staleMissions.length>0};
  }
}
