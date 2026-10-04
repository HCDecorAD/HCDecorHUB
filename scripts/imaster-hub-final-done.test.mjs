import assert from "node:assert/strict";import {buildContentPack} from "../lib/content-factory.mjs";import {evaluateWinner} from "../lib/winner-loop.mjs";import {socialHandoff} from "../lib/social-handoff.mjs";import {scaleDecision} from "../lib/scale-engine.mjs";
const pack=buildContentPack({topic:"compact interior",angle:"space saving",hooks:["3 mistakes","before after","5 fixes"],platforms:["youtube","facebook"],provenance:["trend:evidence"]});assert.equal(pack.items.length,6);
let w=evaluateWinner({baseline:100,current:170,sample_size:3,verified:true,evidence:["analytics"]});assert.equal(w.status,"SCALE");
const hand=socialHandoff(pack,w);assert.equal(hand.status,"READY");assert.equal(hand.publish,false);assert.ok(hand.jobs.every(x=>x.status==="READY_FOR_EXISTING_PUBLISHING"));
assert.equal(scaleDecision({winner:w,analytics_verified:true,publish_receipts:[]}).status,"HOLD");
assert.equal(scaleDecision({winner:w,analytics_verified:true,publish_receipts:["receipt:1"]}).status,"SCALE_READY");
w=evaluateWinner({baseline:100,current:999,sample_size:2,verified:true});assert.equal(w.status,"HOLD");assert.equal(socialHandoff(pack,w).status,"BLOCKED_NO_WINNER");
console.log("IMASTER_HUB_FINAL_DONE gate PASS");