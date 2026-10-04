import assert from"node:assert/strict";import{pandaLiveGate,terminalEvidence}from"../lib/panda-24x7-live-gate.mjs";
const partial=pandaLiveGate({auto_wake:true,post_merge_verify:true});assert.equal(partial.live,false);
const all={auto_wake:true,post_merge_verify:true,durable_restart:true,missed_event_recovery:true,duplicate_safe:true,bounded_repair:true,owner_boundary:true,soak:true};assert.equal(pandaLiveGate(all).status,"PANDA_24_7_LIVE");
assert.equal(terminalEvidence({mission_state:"DONE",receipts:["r"],main_verify:true}).done,true);
assert.equal(terminalEvidence({mission_state:"DONE",receipts:[],main_verify:true}).done,false);
console.log("PANDA 24/7 final acceptance contract PASS");