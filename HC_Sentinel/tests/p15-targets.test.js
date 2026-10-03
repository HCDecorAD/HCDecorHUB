import test from "node:test";import assert from "node:assert/strict";import {TargetRegistry} from "../core/target-registry.js";
test("real target profile keeps desktop and mobile",()=>{const r=new TargetRegistry();const t=r.register({id:"gsc",url:"https://gscsenior.hcdecorhub.com"});assert.equal(t.viewports.mobile.width,390);assert.equal(r.enabled().length,1);});
test("target requires url",()=>{assert.throws(()=>new TargetRegistry().register({id:"x"}),/TARGET_ID_URL_REQUIRED/);});
