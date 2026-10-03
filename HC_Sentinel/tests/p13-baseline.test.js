import test from "node:test";import assert from "node:assert/strict";import {BaselineManager} from "../core/baseline-manager.js";
test("baseline is not compare-ready until approved",()=>{const b=new BaselineManager();b.set("gsc","mobile",{revision:"abc",approved:false});assert.equal(b.canCompare("gsc","mobile"),false);assert.equal(b.approve("gsc","mobile","abc"),true);assert.equal(b.canCompare("gsc","mobile"),true);});
test("wrong revision cannot approve baseline",()=>{const b=new BaselineManager();b.set("gsc","desktop",{revision:"a"});assert.equal(b.approve("gsc","desktop","b"),false);});
