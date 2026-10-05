import assert from 'node:assert/strict';
import { runDualLane } from './dual-lane.mjs';
let mcp=0,rdc=0,verify=0;
const result=await runDualLane({kind:'send',target:'acceptance'}, {
 mcp:async()=>{mcp++;return {ok:false,error:'WRITE_UNAVAILABLE'}},
 rdc:async()=>{rdc++;return {ok:true,sent:'Hello'}},
 verify:async()=>{verify++;return {ok:true,observed:'Hello'}}
});
assert.equal(result.ok,true); assert.equal(result.lane,'rdc');
assert.deepEqual([mcp,rdc,verify],[1,1,1]);
console.log('DUAL_LANE_ACCEPTANCE_PASS MCP_FAIL->RDC_WRITE->VERIFY');
