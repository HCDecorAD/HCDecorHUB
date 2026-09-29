import {crossWorkspaceCheck} from "../../../../lib/cross-workspace-check";
export async function GET(){const x=await crossWorkspaceCheck();return Response.json(x,{status:x.ok?200:409})}
