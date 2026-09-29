import {promises as fs} from "node:fs";
import path from "node:path";
const DIR=path.join(process.cwd(),".runtime");const FILE=path.join(DIR,"master-runs.json");const MAX=200;
const clean=v=>v&&typeof v==="object"&&!Array.isArray(v)?v:null;
export async function appendMasterRun(run){const item=clean(run);if(!item)return false;try{await fs.mkdir(DIR,{recursive:true});let rows=[];try{const parsed=JSON.parse(await fs.readFile(FILE,"utf8"));if(Array.isArray(parsed))rows=parsed}catch{}rows.push(item);rows=rows.slice(-MAX);await fs.writeFile(FILE,JSON.stringify(rows,null,2),"utf8");return true}catch{return false}}
export async function listMasterRuns(limit=30,workspaceId=""){const n=Math.max(1,Math.min(Number.isFinite(limit)?Math.trunc(limit):30,100));const workspace=typeof workspaceId==="string"?workspaceId.trim().toLowerCase().slice(0,80):"";try{const parsed=JSON.parse(await fs.readFile(FILE,"utf8"));if(!Array.isArray(parsed))return [];const rows=workspace?parsed.filter(x=>x&&x.workspace_id===workspace):parsed;return rows.slice(-n).reverse()}catch{return []}}
export const masterRunStoreInfo=()=>({mode:"local-spool",max:MAX,productionAuthority:false});
