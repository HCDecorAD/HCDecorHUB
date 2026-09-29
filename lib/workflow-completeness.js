export const REQUIRED_WORKFLOW_MODULES=["content","media","publishing"];
export function workflowCompleteness(artifacts=[]){
 const prepared=[...new Set(artifacts.filter(x=>x?.status==="preview"&&x?.artifact?.state==="prepared").map(x=>x.module))];
 const missing=REQUIRED_WORKFLOW_MODULES.filter(x=>!prepared.includes(x));
 return {complete:missing.length===0,required:REQUIRED_WORKFLOW_MODULES,prepared,missing};
}
