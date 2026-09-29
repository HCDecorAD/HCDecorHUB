import fs from "node:fs";
import path from "node:path";
const ROOT=process.cwd();
const read=(name)=>JSON.parse(fs.readFileSync(path.join(ROOT,"config",name),"utf8").replace(/^\uFEFF/,""));
export function getHubConfig(){return {agents:read("agents.json"),workspaces:read("workspaces.json"),adapters:read("adapters.json"),integrations:read("integrations.json")}}
export function getWorkspaceView(){const {workspaces,adapters}=getHubConfig();return workspaces.workspaces.map(w=>{const a=adapters.adapters[w.site_id]||{},repo=w.repositories?.[0]?.repository||a.repository||null,production=w.domains?.find(x=>x.role==="production")?.url||a.production_url||null;return {...w,adapter:a.type||"unknown",configState:a.config_state||a.source_state||"unknown",productionUrl:production,repository:repo,hosting:w.hosting||null,policy:w.policy||null,rule:a.rule||null,sourceKind:"config"}})}
export function getIntegrationView(){const {integrations}=getHubConfig();return Object.entries(integrations.integrations).map(([id,v])=>({id,state:v.config_state||"unknown",purpose:v.purpose||[],note:v.note||"",sourceKind:"config"}))}