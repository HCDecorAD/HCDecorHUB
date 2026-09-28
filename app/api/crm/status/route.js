import {CRM,crmRuntime} from "../../../../lib/crm/config";
export async function GET(){const r=crmRuntime();return Response.json({ok:r.configured,provider:r.provider||null,storage:r.configured?"google-sheets":null,sheetConfigured:r.configured,writeEnabled:r.writeEnabled,projectProvisionEnabled:r.projectProvisionEnabled,tabs:[CRM.leadsTab,CRM.projectsTab]})}
