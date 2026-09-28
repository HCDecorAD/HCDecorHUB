import fs from "node:fs";
import path from "node:path";
const ROOT=process.cwd();
const read=(name)=>JSON.parse(fs.readFileSync(path.join(ROOT,"config",name),"utf8").replace(/^\uFEFF/,""));
export function getHubConfig(){return {agents:read("agents.json"),workspaces:read("workspaces.json"),adapters:read("adapters.json"),integrations:read("integrations.json")}}
export function getWorkspaceView(){const {workspaces,adapters}=getHubConfig();return workspaces.workspaces.map(w=>{const a=adapters.adapters[w.site_id]||{};return {...w,adapter:a.type||"unknown",configState:a.config_state||a.source_state||"unknown",productionUrl:a.production_url||null,repository:a.repository||null,rule:a.rule||null,sourceKind:"config"}})}
export function getIntegrationView(){const {integrations}=getHubConfig();return Object.entries(integrations.integrations).map(([id,v])=>({id,state:v.config_state||"unknown",purpose:v.purpose||[],note:v.note||"",sourceKind:"config"}))}