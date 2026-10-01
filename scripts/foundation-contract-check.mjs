import fs from "node:fs";import path from "node:path";
const root=process.cwd(),read=p=>JSON.parse(fs.readFileSync(path.join(root,p),"utf8").replace(/^\uFEFF/,""));
let pass=0;
const a=read("config/data-authorities.json");
for(const k of ["crm","master_database","drive"]){if(!a.authorities?.[k]?.provider)throw Error("authority missing "+k);pass++}
if(a.policy?.production_write!=="approval-required"||a.policy?.local_spool_is_production_authority!==false)throw Error("data authority policy unsafe");pass++;
const iam=read("config/identity-access.json");
if(iam.rules?.default!=="deny"||iam.rules?.production_mutation!=="explicit-approval"||iam.rules?.service_credentials!=="server-side-only")throw Error("identity policy unsafe");pass++;
const ir=fs.readFileSync(path.join(root,"lib/identity-runtime.js"),"utf8");
for(const x of ["authenticated:false","defaultDeny:true","productionAuthority:false","authenticated_executor"]){if(!ir.includes(x))throw Error("identity boundary missing "+x)}pass++;
const dr=fs.readFileSync(path.join(root,"docs/OPERATIONS-DR-BASELINE.md"),"utf8");
if(!dr.includes("Off-device")||!dr.includes("RPO")||!dr.includes("RTO"))throw Error("DR baseline incomplete");pass++;
console.log("FOUNDATION CONTRACT PASS "+pass+"/"+pass);
console.log("production_mutation=LOCKED identity=DEFAULT_DENY local_spool_authority=false");