// Server-side data adapter. Demo arrays are intentionally forbidden here.
// Current durable adapters: WordPress CMS (read) + Google Drive references.
// Writes remain disabled until an authenticated durable runtime is configured.
export function runtimeCapabilities(){
 return {
  cmsRead:Boolean(process.env.HCDECOR_WP_BASE_URL),
  cmsWrite:Boolean(process.env.HCDECOR_WP_API_TOKEN),
  driveConfigured:Boolean(process.env.HCDECOR_DRIVE_ROOT_FOLDER_ID),
  driveWrite:false,
  leadWrite:false,
  projectWrite:false,
  agentExternal:Boolean(process.env.HCDECOR_AGENT_BASE_URL)
 };
}
export function assertWritable(capability){
 const c=runtimeCapabilities();
 if(!c[capability]){const e=new Error("durable_runtime_not_configured");e.code="DURABLE_RUNTIME_REQUIRED";throw e}
}