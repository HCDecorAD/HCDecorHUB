/**
 * Identity/RBAC boundary.
 *
 * The configuration defines roles and default-deny policy. Runtime authentication
 * is intentionally not inferred from headers, local state, or environment flags.
 * A real authenticated principal provider must be bound before production
 * mutation can be authorized.
 */
export function unauthenticatedPrincipal(){
 return {authenticated:false,principal_id:null,role:"viewer",workspace_ids:[],store_ids:[],grants:[]};
}
export function hasExplicitGrant(principal,grant){
 return Boolean(principal?.authenticated===true&&Array.isArray(principal.grants)&&principal.grants.includes(grant));
}
export function productionMutationAuthorized(principal){
 return Boolean(principal?.authenticated===true&&principal?.production_approval===true&&principal?.authenticated_executor===true);
}
export function identityRuntimeStatus(){
 return {provider:null,available:false,defaultDeny:true,productionAuthority:false};
}
