import assert from "node:assert/strict";import {nextControllerState,buildWatchRecord} from "../lib/imaster-persistent-controller.mjs";
assert.deepEqual(nextControllerState({mission:{state:"CI_RUNNING"}}),{action:"ARM_WATCH",state:"CI_RUNNING"});
assert.equal(nextControllerState({mission:{state:"READY"},event:{type:"CHECK_FAILED"}}).action,"REPAIR");
assert.equal(nextControllerState({mission:{state:"READY"},event:{type:"MERGED"}}).state,"POST_MERGE_VERIFY");
assert.equal(nextControllerState({mission:{state:"DONE"}}).action,"STOP");
assert.equal(buildWatchRecord({mission_id:"m1",state:"CI_RUNNING",target:"sha"}).armed,true);
console.log("persistent controller contract PASS");
