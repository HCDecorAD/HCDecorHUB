import assert from "node:assert/strict";import {buildContentPack,promoteWinner} from "../lib/content-factory.mjs";
const p=buildContentPack({topic:"small bedroom",angle:"space saving",hooks:["3 mistakes","before/after","5 tricks"],platforms:["youtube","tiktok"],provenance:["trend:123"]});
assert.equal(p.status,"READY_FOR_AI_DRAFT");assert.equal(p.items.length,6);assert.ok(p.items.every(x=>x.status==="DRAFT"));
assert.equal(buildContentPack({topic:"x",angle:"y",hooks:["one"]}).status,"INSUFFICIENT_BRIEF");
assert.equal(promoteWinner(p,{verified:false,sample_size:9,lift_vs_baseline:3}).scale_status,"HOLD");
assert.equal(promoteWinner(p,{verified:true,sample_size:3,lift_vs_baseline:1.3}).scale_status,"WINNER");
console.log("content factory gate PASS");