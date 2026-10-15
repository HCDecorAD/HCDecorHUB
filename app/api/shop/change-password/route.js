import {handleShopChangePassword} from "../../../../lib/shop-admin";
import {getAuthEnv,getUsersKv} from "../../../../lib/auth-env";
export const dynamic="force-dynamic";
// Chỉ cho trang cửa hàng AMO gọi (CORS allowlist).
const ALLOWED=["https://amonguyen.com","https://www.amonguyen.com"];
const originOf=req=>{const o=req.headers.get("origin")||"";return ALLOWED.includes(o)?o:null};
export async function POST(request){return handleShopChangePassword({request,env:await getAuthEnv(),kv:await getUsersKv(),origin:originOf(request)})}
export async function OPTIONS(request){const o=originOf(request);return new Response(null,{status:o?204:403,headers:o?{"access-control-allow-origin":o,"access-control-allow-methods":"POST,OPTIONS","access-control-allow-headers":"content-type,authorization","vary":"Origin","access-control-max-age":"600"}:{}})}
