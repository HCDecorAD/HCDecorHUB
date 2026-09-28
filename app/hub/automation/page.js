import {HubShell,PageCards} from "../../../components/HubShell";
import {getHubConfig} from "../../../lib/hub-config";
export default function Automation(){
 const {agents}=getHubConfig();
 return <HubShell title="Automation" eyebrow="MASTER AGENT / SAFE EXECUTION"><div className="notice">Không có runtime queue source được khai báo trong config; HUB không hiển thị Ready/Active giả.</div><PageCards items={[
  ["ROUTING",agents.routing.strategy,"Capability + workspace routing từ config/agents.json.","/hub/agents"],
  ["PARALLEL",agents.routing.parallel_safe_tasks?"Safe tasks enabled":"Disabled","Same-resource writes: "+agents.routing.same_resource_writes],
  ["PRODUCTION GATE",agents.safety.production_requires_gate?"Required":"Not configured","Publish/deploy/delete cần permission gate.","/hub/review"],
  ["RUNTIME QUEUE","Not configured","Chưa có nguồn telemetry xác minh cho queue totals."]
 ]}/></HubShell>
}
