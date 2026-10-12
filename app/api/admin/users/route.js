import {handleAdminUsers} from "../../../../lib/user-routes";
import {getUsersKv} from "../../../../lib/auth-env";
import {getPrincipal,canViewSuperAdmin} from "../../../../lib/superadmin";
export const dynamic="force-dynamic";
async function run(request){return handleAdminUsers({request,kv:await getUsersKv(),canAdmin:canViewSuperAdmin(await getPrincipal())})}
export const GET=run,POST=run;
