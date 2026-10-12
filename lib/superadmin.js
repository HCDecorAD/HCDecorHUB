import registry from "../config/site-registry.json";
import permissions from "../config/permissions.json";
import {getWorkspaceView} from "./hub-config";
import {cookies} from "next/headers";
import {unauthenticatedPrincipal} from "./identity-runtime";
import {resolveGithubSessionPrincipal,SESSION_COOKIE} from "./github-auth";
import {getAuthEnv} from "./auth-env";

// Read-only access: only an authenticated principal with role owner (scope all, action view).
// Provider: signed GitHub session cookie (see github-auth.js); any failure denies by default.
export async function getPrincipal(){
 try{
  const jar=await cookies();
  return await resolveGithubSessionPrincipal({cookieValue:jar.get(SESSION_COOKIE)?.value,env:await getAuthEnv()});
 }catch{return unauthenticatedPrincipal()}
}
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
