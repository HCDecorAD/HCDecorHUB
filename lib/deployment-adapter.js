import {getHubConfig} from "./hub-config";
import {deploymentProfileFromConfig,deploymentPlanFromProfile} from "./deployment-adapter-core.mjs";

export function getDeploymentProfile(workspaceId){
 return deploymentProfileFromConfig(getHubConfig(),workspaceId);
}
export function planDeployment(workspaceId,options={}){
 return deploymentPlanFromProfile(getDeploymentProfile(workspaceId),options);
}
