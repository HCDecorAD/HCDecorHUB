import {HubShell,PageCards} from "../../../components/HubShell";
import {getHubConfig,getIntegrationView} from "../../../lib/hub-config";
const title=s=>s.charAt(0).toUpperCase()+s.slice(1);
export default function AI(){
 const {agents}=getHubConfig();
 const integrations=getIntegrationView();
 const external=Object.entries(agents.external_workers).map(([id,v])=>[title(id),v.role,v.authority+" · "+v.merge,"/hub/agents"]);
 const source=integrations.filter(x=>["github","wordpress","vercel"].includes(x.id)).map(x=>[title(x.id),x.state,x.purpose.join(" · "),"/admin/integrations"]);
 return <HubShell title="AI Providers" eyebrow="MASTER AGENT / SPECIALISTS"><div className="notice">External AI providers are optional specialist workers. They do not control HUB architecture or merge changes independently.</div><PageCards items={[...external,...source]}/></HubShell>
}
