import {getArchitectureBaseline} from "./architecture-baseline";
import {getWorkspaceView} from "./hub-config";
import {getDeploymentProfile} from "./deployment-adapter";
import {runtimeCapabilities} from "./data/store";

export function operationalContract(){
 const baseline=getArchitectureBaseline(),capabilities=runtimeCapabilities(),views=getWorkspaceView();
 const readCapabilities=["cmsRead"],writeCapabilities=["cmsWrite","driveWrite","leadWrite","projectWrite"];
 const readReady=readCapabilities.some(k=>capabilities[k]===true),durableWriteReady=writeCapabilities.some(k=>capabilities[k]===true);
 const workspaces=views.map(w=>{const deployment=getDeploymentProfile(w.workspace_id);return {workspace_id:w.workspace_id,site_id:w.site_id,config_state:w.configState,production_url:w.productionUrl,repository:w.repository,hosting_provider:deployment.ok?deployment.providers.primary?.provider||null:null,source_ready:Boolean(w.productionUrl&&(w.repository||w.adapter==="wordpress")),runtime_read_ready:w.workspace_id==="hcdecor"?readReady:false,durable_write_runtime_ready:w.workspace_id==="hcdecor"?durableWriteReady:false,production_write:false}});
 const sourceReady=workspaces.filter(w=>w.source_ready).length;
 return {ok:true,architecture:{baseline_id:baseline.baseline_id,status:baseline.status,principle:baseline.principle},workspaces,summary:{workspaces:workspaces.length,source_ready:sourceReady,read_capabilities:readCapabilities.filter(k=>capabilities[k]).length,write_capabilities:writeCapabilities.filter(k=>capabilities[k]).length,configured_capabilities:Object.values(capabilities).filter(Boolean).length,architecture_locked:baseline.status==="locked",operational_skeleton_ready:baseline.status==="locked"&&sourceReady===workspaces.length},capabilities,truth_contract:{source_ready_is_not_live_health:true,runtime_read_ready_is_not_write_ready:true,durable_write_runtime_ready_is_not_production_authority:true,production_write:false}};
}
