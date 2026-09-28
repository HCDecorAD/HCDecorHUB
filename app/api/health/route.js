import {runtimeCapabilities} from "../../../lib/data/store";
import {getIntegrationView} from "../../../lib/hub-config";
export async function GET(){
 const c=runtimeCapabilities();
 const checks={hub:{state:"configured",source:"application"},cms:{state:c.cmsRead?"configured":"unknown",source:"environment"},cmsWrite:{state:c.cmsWrite?"configured":"disabled",source:"environment"},drive:{state:c.driveConfigured?"configured":"unknown",source:"environment"},driveWrite:{state:c.driveWrite?"configured":"disabled",source:"environment"},leadWrite:{state:c.leadWrite?"configured":"disabled",source:"environment"},projectWrite:{state:c.projectWrite?"configured":"disabled",source:"environment"}};
 const integrations=getIntegrationView().map(x=>({provider:x.id,configState:x.state,source:"config/integrations.json",liveHealth:"not_checked"}));
 return Response.json({service:"ok",version:"HCDecor HUB Master Agent v2",checks,integrations,checkedAt:new Date().toISOString(),note:"Configuration state is separate from live provider health."});
}
