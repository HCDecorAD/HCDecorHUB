import test from "node:test";import assert from "node:assert/strict";import {TriggerBroker} from "../core/scheduler.js";import {LeaseRegistry} from "../core/lease.js";
test("manual preempts scheduled",()=>{const b=new TriggerBroker();b.push({mode:"SCHEDULED"});b.push({mode:"MANUAL_NOW"});assert.equal(b.next().mode,"MANUAL_NOW");});
test("expired lease can be reclaimed",()=>{let t=0;const l=new LeaseRegistry({now:()=>t});assert.equal(l.claim("job","A",10),true);assert.equal(l.claim("job","B",10),false);t=11;assert.equal(l.claim("job","B",10),true);});
