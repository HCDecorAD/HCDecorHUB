import {handleCallback} from "../../../../../lib/github-auth";
export const dynamic="force-dynamic";
export function GET(request){return handleCallback({request,env:process.env})}
