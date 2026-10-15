import {handleShopStaff} from "../../../../lib/shop-admin";
import {getAuthEnv,getUsersKv} from "../../../../lib/auth-env";
export const dynamic="force-dynamic";
// Chỉ hc-shop-engine gọi (server-to-server, token quản trị). Không mở CORS cho trình duyệt.
export async function GET(request){return handleShopStaff({request,env:await getAuthEnv(),kv:await getUsersKv()})}
export async function POST(request){return handleShopStaff({request,env:await getAuthEnv(),kv:await getUsersKv()})}
