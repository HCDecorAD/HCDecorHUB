import {FindingLifecycle,FindingState} from "../core/finding-lifecycle.js";import {PolicyEngine} from "../core/policy-engine.js";import {HealthSupervisor} from "../core/health-supervisor.js";import {ReleaseGate} from "../core/release-gate.js";
const findings=new FindingLifecycle();findings.create({id:"gsc-broken-media",severity:"medium",confidence:.9});findings.transition("gsc-broken-media",FindingState.ROUTED,{issue:1265});
const policy=new PolicyEngine().decide(findings.unresolved()[0]);
const health=new HealthSupervisor().evaluate({workers:[{id:"autodebug-ui",state:"HEALTHY"}],targets:[{id:"gsc",ok:true},{id:"amo",ok:true}],staleMissions:[]});
const gate=new ReleaseGate().evaluate({tests:{ok:true},findings:findings.unresolved()});
const result={findingState:findings.unresolved()[0].state,policy,health:health.status,release:gate.status};
console.log(JSON.stringify(result));if(result.findingState!=="ROUTED"||result.policy.action!=="ROUTE_REPAIR"||result.health!=="HEALTHY"||result.release!=="PASS")process.exit(2);
