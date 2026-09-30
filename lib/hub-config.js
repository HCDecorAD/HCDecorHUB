import agents from "../config/agents.json";
import workspaces from "../config/workspaces.json";
import adapters from "../config/adapters.json";
import integrations from "../config/integrations.json";
export function getHubConfig(){return {agents,workspaces,adapters,integrations}}
export function getWorkspaceView(){return workspaces.workspaces.map(w=>{const a=adapters.adapters[w.site_id]||{},repo=w.repositories?.[0]?.repository||a.repository||null,production=w.domains?.find(x=>x.role==="production")?.url||a.production_url||null;return {...w,adapter:a.type||"unknown",configState:a.config_state||a.source_state||"unknown",productionUrl:production,repository:repo,hosting:w.hosting||null,policy:w.policy||null,rule:a.rule||null,sourceKind:"bundled-config"}})}
export function getIntegrationView(){return Object.entries(integrations.integrations).map(([id,v])=>({id,state:v.config_state||"unknown",purpose:v.purpose||[],note:v.note||"",sourceKind:"bundled-config"}))}
