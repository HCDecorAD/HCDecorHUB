import test from "node:test";import assert from "node:assert/strict";import {ProjectRegistry} from "../core/project-registry.js";
test("project profile stores viewport policy",()=>{const r=new ProjectRegistry();const p=r.register({id:"gsc",profiles:{mobile:{viewport:{width:390,height:844}}}});assert.equal(p.profiles.mobile.viewport.width,390);assert.equal(r.all().length,1);});
