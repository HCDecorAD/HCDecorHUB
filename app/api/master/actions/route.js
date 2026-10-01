import {productionGuard} from "../../../../lib/hub-policy";
const ACTIONS=["view","create","edit","export","approve","publish","delete","manage","deploy","rollback"];
export async function GET(){const guard=productionGuard();return Response.json({ok:true,production_write:false,actions:ACTIONS.map(action=>({action,guarded:guard.guarded.includes(action)||action==="rollback",mode:(guard.guarded.includes(action)||action==="rollback")?"approval-required":"plan-only"}))})}
