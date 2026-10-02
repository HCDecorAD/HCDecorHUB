import assert from 'node:assert/strict';
import {scheduleResourceAware} from '../lib/governor/resource-scheduler.mjs';

const tasks=[
 {task_id:'hc-done',state:'READY',lane:'critical',priority:100,required_capabilities:['done'],resources:{cpu:1},ready_since:'2026-10-02T16:00:00Z'},
 {task_id:'transwarp',state:'READY',lane:'recovery',priority:90,required_capabilities:['transwarp'],resources:{cpu:1},ready_since:'2026-10-02T16:01:00Z'},
 {task_id:'hub-core',state:'READY',priority:80,required_capabilities:['hub'],resources:{cpu:1},ready_since:'2026-10-02T16:02:00Z'},
 {task_id:'gsc',state:'READY',priority:60,required_capabilities:['web'],resources:{cpu:1},ready_since:'2026-10-02T16:03:00Z'},
 {task_id:'amo',state:'BLOCKED',priority:70,required_capabilities:['web'],resources:{cpu:1},ready_since:'2026-10-02T16:03:00Z'}
];
const workers=[
 {worker_id:'w-done',capabilities:['done'],concurrency_limit:1},
 {worker_id:'w-transwarp',capabilities:['transwarp'],concurrency_limit:1},
 {worker_id:'w-hub',capabilities:['hub'],concurrency_limit:1},
 {worker_id:'w-web',capabilities:['web'],concurrency_limit:1}
];
const out=scheduleResourceAware({tasks,workers,resources:{cpu:4},now:Date.parse('2026-10-02T16:30:00Z')});
const dispatched=new Set(out.dispatches.map(x=>x.task_id));
assert.deepEqual(new Set(['hc-done','transwarp','hub-core','gsc']),dispatched);
assert.ok(out.untouched.includes('amo'));
assert.equal(out.dispatches.length,4);
console.log('PORTFOLIO_PILOT_PASS blocked=amo dispatched=hc-done,transwarp,hub-core,gsc stalled_unrelated=0');
