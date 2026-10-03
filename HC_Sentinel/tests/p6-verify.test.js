import test from "node:test";import assert from "node:assert/strict";import {VerifyLoop} from "../core/verify-loop.js";
test("green only after verifier ok",async()=>{const v=new VerifyLoop({verify:async()=>({ok:true}),evidence:async p=>p});assert.equal((await v.run({})).status,"GREEN");});
test("uncertain effect never becomes green",async()=>{const v=new VerifyLoop({verify:async()=>({ok:false,uncertainEffect:true}),evidence:async p=>p});assert.equal((await v.run({})).status,"UNCERTAIN_EFFECT");});
