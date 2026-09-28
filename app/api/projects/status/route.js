import {crmRuntime} from "../../../../lib/crm/config";
export async function GET(){const r=crmRuntime();return Response.json({ok:r.projectProvisionEnabled,storage:r.projectProvisionEnabled?"google-sheets+drive":null,createEnabled:r.projectProvisionEnabled,flow:"Qualified Lead -> Project ID -> Drive Folder -> Projects row"})}
