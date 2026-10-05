import fs from "node:fs";
const root=process.cwd();
const read=p=>JSON.parse(fs.readFileSync(root+"/"+p,"utf8"));
const bootstrap=read("config/imaster-bootstrap.json");
const router=read("config/imaster-skill-router.json");
const skillDir=root+"/config/imaster-skills";
const files=fs.readdirSync(skillDir).filter(x=>x.endsWith(".json"));
const ids=new Map(files.map(f=>{const x=read("config/imaster-skills/"+f);return [x.skill_id||x.id,f]}));
const special=new Set(["IMASTER_CAPABILITY_ACQUISITION"]);
const missing=[];
for(const route of router.routes||[])for(const id of route.skills||[])if(!special.has(id)&&!ids.has(id))missing.push(id);
const errors=[];
if(!(bootstrap.load_order||[]).includes("config/imaster-skill-router.json"))errors.push("router not in bootstrap load_order");
if(bootstrap.skill_router?.full_catalog!=="docs/IMASTER-FULL-SKILL-CATALOG.md")errors.push("FULL catalog mismatch");
if(missing.length)errors.push("missing skill ids: "+[...new Set(missing)].join(", "));
if(errors.length){console.error(JSON.stringify({ok:false,errors},null,2));process.exit(1)}
console.log(JSON.stringify({ok:true,skills:ids.size,routes:(router.routes||[]).length,done_rule:router.done_rule},null,2));
