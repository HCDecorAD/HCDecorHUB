import fs from "node:fs";import path from "node:path";
const read=n=>JSON.parse(fs.readFileSync(path.join(process.cwd(),"config",n),"utf8").replace(/^\uFEFF/,""));
const required=["runtime-contract.json","identity-access.json","workspaces.json","capabilities.json","permissions.json","qa-gates.json","adapters.json","agents.json","audit-schema.json"];
let pass=0;for(const f of required){read(f);pass++}
const ws=read("workspaces.json").workspaces, ids=new Set();
for(const w of ws){if(ids.has(w.workspace_id))throw Error("duplicate workspace "+w.workspace_id);ids.add(w.workspace_id);if(!w.repositories?.length||!w.domains?.length||!w.hosting?.primary||!w.policy)throw Error("workspace contract incomplete "+w.workspace_id);pass++}
const rt=read("runtime-contract.json");if(!rt.tenant_isolation?.fail_closed||rt.release?.verification!=="required"||rt.release?.audit!=="required")throw Error("runtime contract unsafe");pass++;
const iam=read("identity-access.json");if(iam.rules?.default!=="deny"||!iam.rules?.workspace_isolation||!iam.rules?.store_isolation)throw Error("iam fail-open");pass++;
const kernel=fs.readFileSync(path.join(process.cwd(),"lib","orchestration-kernel.js"),"utf8");if(!kernel.includes("prepareRun")||!kernel.includes("fail_closed"))throw Error("orchestration kernel missing");pass++; const policy=fs.readFileSync(path.join(process.cwd(),"lib","policy-engine.js"),"utf8");if(!policy.includes("approval_required")||!policy.includes("workspace_not_granted")||!policy.includes("durable_execution_required")||!policy.includes("HC_DURABLE_EXECUTION_ENABLED"))throw Error("policy engine incomplete");pass++;
const master=fs.readFileSync(path.join(process.cwd(),"app","api","master","route.js"),"utf8");if(!master.includes("durable_execution_required")||!master.includes("HC_DURABLE_EXECUTION_ENABLED"))throw Error("master execution durable gate missing");pass++;\nconsole.log("ARCHITECTURE PASS "+pass+"/"+pass);console.log("workspaces="+ws.map(x=>x.workspace_id).join(","));console.log("runtime="+rt.schema_version+" default-deny="+iam.rules.default);

