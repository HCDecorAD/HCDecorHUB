import {handleLogout} from "../../../../lib/github-auth";
export const dynamic="force-dynamic";
export function GET(){return handleLogout()}
export function POST(){return handleLogout()}
