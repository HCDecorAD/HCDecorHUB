import test from "node:test";import assert from "node:assert/strict";import {mapHcdrIssueState} from "../adapters/repair/hdcr-status-sync.js";
test("open issue without comments remains routed",()=>assert.equal(mapHcdrIssueState({state:"open",comments:0}).state,"ROUTED"));
test("open issue with comments becomes acknowledged",()=>assert.equal(mapHcdrIssueState({state:"open",comments:2}).state,"ACKNOWLEDGED"));
test("closed issue resolves finding",()=>{const r=mapHcdrIssueState({state:"closed",state_reason:"completed",comments:2});assert.equal(r.state,"RESOLVED");assert.equal(r.resolved,true);});
