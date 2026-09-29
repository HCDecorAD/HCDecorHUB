import {runtimeCapabilities} from "./data/store";
import {wordpressConfig} from "./cms/wordpress";
import {crmRuntime} from "./crm/config";
import {authorityIds} from "./data-authorities";
const present=v=>Boolean(String(v||"").trim());
export function runtimeDiagnostics(){
 const caps=runtimeCapabilities(),wp=wordpressConfig(),crm=crmRuntime(),authority=authorityIds();
 const requirements={
  cmsRead:{ready:caps.cmsRead,missing:[!wp.siteUrl&&"HCDECOR_WP_BASE_URL"].filter(Boolean)},
  cmsWrite:{ready:caps.cmsWrite,missing:[!present(process.env.HCDECOR_WP_API_TOKEN)&&"HCDECOR_WP_API_TOKEN"].filter(Boolean)},
  driveConfigured:{ready:caps.driveConfigured,missing:[!authority.driveRootId&&!present(process.env.HCDECOR_DRIVE_ROOT_FOLDER_ID)&&"HCDECOR_DRIVE_ROOT_FOLDER_ID"].filter(Boolean)},
  driveWrite:{ready:caps.driveWrite,missing:[!present(process.env.GOOGLE_SERVICE_ACCOUNT_JSON)&&"GOOGLE_SERVICE_ACCOUNT_JSON"].filter(Boolean)},
  leadWrite:{ready:caps.leadWrite,missing:[crm.provider!=="google-sheets-api"&&"HCDECOR_CRM_WRITE_PROVIDER",!present(process.env.GOOGLE_SERVICE_ACCOUNT_JSON)&&"GOOGLE_SERVICE_ACCOUNT_JSON"].filter(Boolean)},
  projectWrite:{ready:caps.projectWrite,missing:[crm.provider!=="google-sheets-api"&&"HCDECOR_CRM_WRITE_PROVIDER",!present(process.env.GOOGLE_SERVICE_ACCOUNT_JSON)&&"GOOGLE_SERVICE_ACCOUNT_JSON",!present(process.env.HCDECOR_PROJECT_API_TOKEN)&&"HCDECOR_PROJECT_API_TOKEN"].filter(Boolean)}
 };
 const setup_order=["cmsRead","driveConfigured","cmsWrite","driveWrite","leadWrite","projectWrite"],next=setup_order.find(k=>!requirements[k].ready)||null;
 return {ok:true,capabilities:caps,requirements,setup_order,next_requirement:next?{capability:next,missing:requirements[next].missing}:null,configured_count:Object.values(caps).filter(Boolean).length,total:Object.keys(caps).length,secrets_exposed:false,credentials_validated:false,production_write:false};
}
