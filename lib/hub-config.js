import fs from "node:fs";
import path from "node:path";

const ROOT=process.cwd();
const read=(name)=>JSON.parse(fs.readFileSync(path.join(ROOT,"config",name),"utf8"));

export function getHubConfig(){
  const agents=read("agents.json");
  const workspaces=read("workspaces.json");
  const adapters=read("adapters.json");
  const integrations=read("integrations.json");
  return {agents,workspaces,adapters,integrations};
}

export function getWorkspaceView(){
  const {workspaces,adapters}=getHubConfig();
  return workspaces.workspaces.map((w)=>{
    const adapter=adapters.adapters[w.site_id]||{};
    return {
      ...w,
      adapterType:adapter.type||"unknown",
      productionUrl:adapter.production_url||null,
      sourceState:adapter.status||"unknown",
      repository:adapter.repository||null,
      rule:adapter.rule||null
    };
  });
}

export function getIntegrationView(){
  const {integrations}=getHubConfig();
  return Object.entries(integrations.integrations).map(([id,value])=>({
    id,
    state:value.state||value.status||"unknown",
    purpose:Array.isArray(value.purpose)?value.purpose:[],
    note:value.note||null
  }));
}
