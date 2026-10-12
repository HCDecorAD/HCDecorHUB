import {handleLogin} from "../../../../../lib/github-auth";
import {getAuthEnv} from "../../../../../lib/auth-env";
export const dynamic="force-dynamic";
export async function GET(){return handleLogin({env:await getAuthEnv()})}
