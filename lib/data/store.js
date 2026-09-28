// Server-side runtime capability registry. Volatile or fabricated data sources are not accepted.
export function runtimeCapabilities(){
 const crmProvider=process.env.HCDECOR_CRM_WRITE_PROVIDER||"";
 const serviceAccount=Boolean(process.env.GOOGLE_SERVICE_ACCOUNT_JSON);
 const crmSheet=Boolean(process.env.HCDECOR_CRM_SHEET_ID);
 const projectFolder=Boolean(process.env.HCDECOR_DRIVE_PROJECTS_FOLDER_ID);
 return {
  cmsRead:Boolean(process.env.HCDECOR_WP_BASE_URL),
  cmsWrite:Boolean(process.env.HCDECOR_WP_API_TOKEN),
  driveConfigured:Boolean(process.env.HCDECOR_DRIVE_ROOT_FOLDER_ID),
  driveWrite:serviceAccount,
  leadWrite:crmProvider==="google-sheets-api"&&serviceAccount&&crmSheet,
  projectWrite:crmProvider==="google-sheets-api"&&serviceAccount&&crmSheet&&projectFolder,
  agentExternal:Boolean(process.env.HCDECOR_AGENT_BASE_URL)
 };
}
export function assertWritable(capability){const c=runtimeCapabilities();if(!c[capability]){const e=new Error("durable_runtime_not_configured");e.code="DURABLE_RUNTIME_REQUIRED";throw e}}
