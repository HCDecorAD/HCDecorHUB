import {HubShell} from "../../../components/HubShell";
import {aiConnectionStatus} from "../../../lib/ai/connections";
import AIConnectionsClient from "./AIConnectionsClient";
export default function AI(){return <HubShell title="AI & Connections" eyebrow="WIN_AI_CONNECTIONS / SERVER-SIDE CONNECTION CONTROL"><AIConnectionsClient initial={{service:"ok",...aiConnectionStatus(),liveHealth:"not_checked"}}/></HubShell>}
