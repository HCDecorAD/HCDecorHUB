import test from "node:test";import assert from "node:assert/strict";import {Heartbeat} from "../core/heartbeat.js";
test("heartbeat reports healthy then stale",()=>{let now=1000;const h=new Heartbeat({ttlMs:100,now:()=>now});h.beat("runtime");assert.equal(h.status("runtime").state,"HEALTHY");now=1201;assert.equal(h.status("runtime").state,"STALE");});
test("unknown heartbeat is explicit",()=>{const h=new Heartbeat();assert.equal(h.status("missing").state,"UNKNOWN");});
