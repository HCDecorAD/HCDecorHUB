import {resolve} from "node:path";
import {createLocalServer} from "./local-server.js";
import {JsonStore} from "../core/json-store.js";
import {EvidenceIndex} from "../core/evidence-index.js";
import {SettingsStore} from "../core/settings-store.js";
import {FindingStore} from "../core/finding-store.js";
import {bootstrap} from "./bootstrap.js";

const root=resolve(new URL("..",import.meta.url).pathname);
await bootstrap({root});
const evidence=new EvidenceIndex(new JsonStore(resolve(root,"data/live/evidence.json")));
const settings=new SettingsStore(new JsonStore(resolve(root,"data/live/settings.json")));
const findings=new FindingStore(new JsonStore(resolve(root,"data/live/findings.json")));
await findings.upsert({id:"gsc-1265",projectId:"gsc",state:"ROUTED",severity:"medium",externalIssue:1265});

const controller={run:async command=>({status:"RUNNING",command})};
const server=createLocalServer({
  controller,
  status:()=>({status:"SENTINEL_READY",version:"0.8.0"}),
  evidence,settings,findings,
  uiDir:new URL("../ui/",import.meta.url)
});
const port=Number(process.env.HC_SENTINEL_PORT||43110);
server.listen(port,"127.0.0.1",()=>console.log(`HC Sentinel local server http://127.0.0.1:${port}`));
