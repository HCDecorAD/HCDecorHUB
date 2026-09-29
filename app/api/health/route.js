import {runtimeCapabilities} from "../../../lib/data/store";
import {getIntegrationView} from "../../../lib/hub-config";
import {approvalStats} from "../../../lib/approval-store";
import {masterRunStoreInfo} from "../../../lib/run-store";
export async function GET(){
 const c=runtimeCapabilities();
 const checks={hub:{state:"configured",source:"application"},cms:{state:c.cmsRead?"configured":"unknown",source:"environment"},cmsWrite:{state:c.cmsWrite?"configured":"disabled",source:"environment"},drive:{state:c.driveConfigured?"configured":"unknown",source:"environment"},driveWrite:{state:c.driveWrite?"configured":"disabled",source:"environment"},leadWrite:{state:c.leadWrite?"configured":"disabled",source:"environment"},projectWrite:{state:c.projectWrite?"configured":"disabled",source:"environment"}};
 const master={runStore:masterRunStoreInfo(),approvals:await approvalStats(),productionWrite:false};
 const integrations=getIntegrationView().map(x=>({provider:x.id,configState:x.state,source:"config/integrations.json",liveHealth:"not_checked"}));
 return Response.json({service:"ok",version:"HCDecor HUB Master Agent v2",checks,master,integrations,checkedAt:new Date().toISOString(),note:"Configuration state is separate from live provider health."});
}
