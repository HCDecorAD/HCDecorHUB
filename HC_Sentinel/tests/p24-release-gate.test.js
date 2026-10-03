import test from "node:test";import assert from "node:assert/strict";import {ReleaseGate} from "../core/release-gate.js";
test("medium open finding does not block release",()=>{const r=new ReleaseGate().evaluate({tests:{ok:true},findings:[{severity:"medium",state:"ROUTED"}]});assert.equal(r.status,"PASS");});
test("critical unresolved finding blocks release",()=>{const r=new ReleaseGate().evaluate({tests:{ok:true},findings:[{severity:"critical",state:"OPEN"}]});assert.equal(r.status,"BLOCKED");});
test("failed tests block release",()=>assert.equal(new ReleaseGate().evaluate({tests:{ok:false},findings:[]}).status,"BLOCKED"));
