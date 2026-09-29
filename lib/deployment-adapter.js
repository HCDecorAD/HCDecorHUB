import {getHubConfig} from "./hub-config";

export function getDeploymentProfile(workspaceId){
 const {workspaces,adapters}=getHubConfig();
 const w=workspaces.workspaces.find(x=>x.workspace_id===String(workspaceId||"").toLowerCase());
 if(!w)return {ok:false,error:"workspace_not_found"};
 const adapter=adapters.adapters[w.site_id]||{};
 const primary=w.hosting?.primary||null,fallback=w.hosting?.fallback||null,preview=w.hosting?.preview||null;
 return {ok:true,workspace_id:w.workspace_id,site_id:w.site_id,repository:w.repositories?.[0]?.repository||adapter.repository||null,production_url:w.domains?.find(x=>x.role==="production")?.url||adapter.production_url||null,providers:{primary,fallback,preview},source_of_truth:"github-main-or-workspace-repository",provider_change:"adapter-change-not-core-change",production_write:false,deploy_requires_approval:true};
}
export function planDeployment(workspaceId,{provider=null,environment="production"}={}){
 const profile=getDeploymentProfile(workspaceId);if(!profile.ok)return profile;
 const selected=provider||profile.providers.primary?.provider||null;
 const known=[profile.providers.primary?.provider,profile.providers.fallback?.provider,profile.providers.preview?.provider].filter(Boolean);
 if(!selected||!known.includes(selected))return {ok:false,error:"deployment_provider_not_configured",workspace_id:profile.workspace_id,production_write:false};
 return {ok:true,workspace_id:profile.workspace_id,provider:selected,environment,repository:profile.repository,production_url:profile.production_url,requires_approval:environment==="production",execution_started:false,production_write:false};
}
