import {resolve} from "node:path";
import {fileURLToPath} from "node:url";
import {readFile} from "node:fs/promises";
import {createLocalServer} from "./local-server.js";
import {createLiveController} from "./live-controller.js";
import {JsonStore} from "../core/json-store.js";
import {EvidenceIndex} from "../core/evidence-index.js";
import {SettingsStore} from "../core/settings-store.js";
import {FindingStore} from "../core/finding-store.js";
import {LogStore} from "../core/log-store.js";
import {ProjectRegistry} from "../core/project-registry.js";
import {PlaywrightObserver} from "../adapters/browser/playwright-observer.js";
import {inspectSnapshot} from "../core/quality.js";
import {bootstrap} from "./bootstrap.js";

const root=resolve(fileURLToPath(new URL("..",import.meta.url)));
await bootstrap({root});
const pkg=JSON.parse(await readFile(resolve(root,"package.json"),"utf8"));
const targetConfig=JSON.parse(await readFile(resolve(root,"data/targets.json"),"utf8"));
const evidence=new EvidenceIndex(new JsonStore(resolve(root,"data/live/evidence.json")));
const settings=new SettingsStore(new JsonStore(resolve(root,"data/live/settings.json")));
const findings=new FindingStore(new JsonStore(resolve(root,"data/live/findings.json")));
const logs=new LogStore(new JsonStore(resolve(root,"logs/runtime.json")));
const projects=new ProjectRegistry();
for(const t of targetConfig.targets)if(!projects.get(t.projectId))projects.register({id:t.projectId,name:t.projectId.toUpperCase()});
const observer=new PlaywrightObserver();
const controller=createLiveController({
  projects,targets:targetConfig.targets,observer,inspectSnapshot,evidence,findings,logs,
  evidenceDir:resolve(root,"evidence/live")
});
await logs.append({level:"info",type:"STARTUP",message:`HC Sentinel runtime ${pkg.version} started`});

const server=createLocalServer({
  controller,
  status:()=>({status:"SENTINEL_READY",version:pkg.version}),
  evidence,settings,findings,logs,
  uiDir:new URL("../ui/",import.meta.url)
});
const port=Number(process.env.HC_SENTINEL_PORT||43110);
server.listen(port,"127.0.0.1",()=>console.log(`HC Sentinel local server http://127.0.0.1:${port}`));
