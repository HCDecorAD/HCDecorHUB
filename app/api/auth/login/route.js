import {handleUserLogin} from "../../../../lib/user-routes";
import {getAuthEnv,getUsersKv} from "../../../../lib/auth-env";
export const dynamic="force-dynamic";
export async function POST(request){return handleUserLogin({request,env:await getAuthEnv(),kv:await getUsersKv()})}
