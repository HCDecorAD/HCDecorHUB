import assert from "node:assert/strict";import {evaluateWinner,nextWinnerActions} from "../lib/winner-loop.mjs";
let r=evaluateWinner({baseline:100,current:170,sample_size:5,verified:true,evidence:["analytics"]});assert.equal(r.status,"SCALE");assert.equal(nextWinnerActions(r)[0],"CREATE_VARIANTS");
r=evaluateWinner({baseline:100,current:125,sample_size:3,verified:true});assert.equal(r.status,"VARIANT_TEST");
r=evaluateWinner({baseline:100,current:80,sample_size:8,verified:true});assert.equal(r.status,"STOP");
r=evaluateWinner({baseline:100,current:999,sample_size:9,verified:false});assert.equal(r.status,"HOLD");
console.log("winner loop gate PASS");