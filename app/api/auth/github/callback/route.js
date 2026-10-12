import {handleCallback} from "../../../../../lib/github-auth";
import {getAuthEnv} from "../../../../../lib/auth-env";
export const dynamic="force-dynamic";
export async function GET(request){return handleCallback({request,env:await getAuthEnv()})}
