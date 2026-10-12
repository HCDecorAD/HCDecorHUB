import {handleLogin} from "../../../../../lib/github-auth";
export const dynamic="force-dynamic";
export function GET(){return handleLogin({env:process.env})}
