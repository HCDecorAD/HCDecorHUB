import test from "node:test";import assert from "node:assert/strict";import {AlertDedupe} from "../core/alert-dedupe.js";
test("duplicate alert is suppressed inside window",()=>{let now=0;const d=new AlertDedupe({windowMs:100,now:()=>now});assert.equal(d.accept("gsc"),true);now=50;assert.equal(d.accept("gsc"),false);now=101;assert.equal(d.accept("gsc"),true);});
