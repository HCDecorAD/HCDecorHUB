import fs from 'node:fs';
import assert from 'node:assert/strict';

const x=JSON.parse(fs.readFileSync('config/lifecycle-registry.json','utf8'));
assert.equal(x.schema_version,'1.0.0');
assert.ok(Array.isArray(x.hierarchy)&&x.hierarchy.includes('goal')&&x.hierarchy.includes('mission')&&x.hierarchy.includes('worker_or_tool')&&x.hierarchy.includes('evidence')&&x.hierarchy.includes('checkpoint'));
assert.ok(Array.isArray(x.goals)&&x.goals.length>0);
assert.ok(Array.isArray(x.missions)&&x.missions.length>0);
const goals=new Set(x.goals.map(g=>g.goal_id));
for(const g of x.goals){assert.ok(g.project_id);assert.ok(g.state);assert.ok(g.acceptance_gate);assert.ok(g.checkpoint);assert.ok(Array.isArray(g.missions));}
for(const m of x.missions){assert.ok(m.mission_id);assert.ok(goals.has(m.goal_id),m.mission_id);assert.ok(m.state);assert.ok(Array.isArray(m.provider_tools)&&m.provider_tools.length);assert.ok(m.evidence_gate);assert.ok(m.checkpoint);}
assert.equal(x.worker_contract.ownership_until_terminal,true);
assert.deepEqual(new Set(x.worker_contract.terminal_states),new Set(['DONE','OWNER_REQUIRED','SAFETY_STOP','HARD_BLOCKED']));
assert.equal(x.worker_contract.no_green_without_evidence,true);
for(const k of ['mission_id','correlation_id','component','outcome']) assert.ok(x.evidence_contract.required_links.includes(k),k);
console.log(`LIFECYCLE_REGISTRY_PASS goals=${x.goals.length} missions=${x.missions.length} worker_contract=1 evidence_links=1`);
