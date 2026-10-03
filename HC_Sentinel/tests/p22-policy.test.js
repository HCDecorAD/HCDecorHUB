import test from "node:test";import assert from "node:assert/strict";import {PolicyEngine} from "../core/policy-engine.js";
const p=new PolicyEngine();
test("medium routes but does not auto repair",()=>{const r=p.decide({severity:"medium",confidence:.9});assert.equal(r.action,"ROUTE_REPAIR");assert.equal(r.autoRepair,false);});
test("high may auto repair",()=>assert.equal(p.decide({severity:"high",confidence:.9}).autoRepair,true));
test("critical blocks release",()=>assert.equal(p.decide({severity:"critical",confidence:.9}).action,"BLOCK_RELEASE"));
test("low confidence requires review",()=>assert.equal(p.decide({severity:"high",confidence:.2}).action,"REVIEW_REQUIRED"));
