import registry from "../config/site-registry.json";
import permissions from "../config/permissions.json";
import {getWorkspaceView} from "./hub-config";
import {unauthenticatedPrincipal} from "./identity-runtime";

// Read-only access: only an authenticated principal with role owner (scope all, action view).
// No principal provider is bound yet (see identity-runtime.js), so this denies by default.
export function getPrincipal(){return unauthenticatedPrincipal()}
export function canViewSuperAdmin(principal){
 const role=permissions.roles.owner;
 return Boolean(principal?.authenticated===true&&principal.role==="owner"&&role?.scope==="all"&&role.actions.includes("view"));
}
export function getSiteRows(){
 const ws=getWorkspaceView();
 return registry.sites.map(s=>{
  const w=ws.find(x=>x.workspace_id===s.workspace_id)||{};
  const domain=w.productionUrl||s.production_url||null;
  const links=[];
  const repo=s.repository||w.repository;
  if(repo)links.push({label:"GitHub",href:"https://github.com/"+repo});
  if(domain)links.push({label:"Production",href:domain});
  return {site_id:s.site_id,name:s.name,domain,hosting:w.hosting?.primary?.provider||s.platform||null,status:s.status||null,healthUrl:s.production_url||null,links};
 });
}
