import test from "node:test";import assert from "node:assert/strict";import {aggregateRuntimeHealth} from "../core/runtime-health.js";
test("runtime health is healthy when all lanes are clean",()=>{const r=aggregateRuntimeHealth({heartbeat:[{state:"HEALTHY"}],workers:[{state:"HEALTHY"}],targets:[{ok:true}],findings:[]});assert.equal(r.state,"HEALTHY");});
test("stale heartbeat dominates",()=>{const r=aggregateRuntimeHealth({heartbeat:[{state:"STALE"}],workers:[],targets:[],findings:[]});assert.equal(r.state,"STALE");});
test("critical finding degrades runtime health",()=>{const r=aggregateRuntimeHealth({heartbeat:[],workers:[],targets:[],findings:[{severity:"critical",state:"OPEN"}]});assert.equal(r.state,"DEGRADED");});
