import fs from 'node:fs';
import assert from 'node:assert/strict';

const x=JSON.parse(fs.readFileSync('config/lifecycle-registry.json','utf8'));
assert.equal(x.schema_version,'1.1.0');
const catalog=JSON.parse(fs.readFileSync('config/corporate-catalog.json','utf8'));
const projects=new Set(catalog.projects.map(p=>p.project_id));
const systems=new Set(catalog.systems.map(s=>s.system_id));
const tools=new Set(catalog.tools.map(t=>t.tool_id));
assert.ok(Array.isArray(x.hierarchy)&&x.hierarchy.includes('goal')&&x.hierarchy.includes('mission')&&x.hierarchy.includes('worker_or_tool')&&x.hierarchy.includes('evidence')&&x.hierarchy.includes('checkpoint'));
assert.ok(Array.isArray(x.goals)&&x.goals.length>0);
assert.ok(Array.isArray(x.missions)&&x.missions.length>0);
const goals=new Set(x.goals.map(g=>g.goal_id));
for(const g of x.goals){assert.ok(g.project_id);assert.ok(projects.has(g.project_id));assert.ok(g.state);assert.ok(g.acceptance_gate);assert.ok(g.checkpoint);assert.ok(Array.isArray(g.missions));assert.ok(Array.isArray(g.system_refs)&&g.system_refs.length);for(const s of g.system_refs)assert.ok(systems.has(s),s);}
for(const m of x.missions){assert.ok(m.mission_id);assert.ok(goals.has(m.goal_id),m.mission_id);assert.ok(projects.has(m.project_id),m.mission_id);assert.ok(m.state);assert.ok(Array.isArray(m.provider_tools)&&m.provider_tools.length);assert.ok(Array.isArray(m.tool_refs)&&m.tool_refs.length);for(const tool of m.tool_refs)assert.ok(tools.has(tool),tool);assert.ok(Array.isArray(m.system_refs)&&m.system_refs.length);for(const s of m.system_refs)assert.ok(systems.has(s),s);assert.ok(Array.isArray(m.evidence_refs)&&m.evidence_refs.length);assert.ok(m.evidence_gate);assert.ok(m.checkpoint);}
const catalogMission=x.missions.find(m=>m.mission_id==='mission-catalog-coverage');
assert.ok(catalogMission);
assert.equal(catalogMission.state,'DONE');
assert.ok(catalogMission.checkpoint.includes('34/34 = 100.00%'));
assert.ok(catalogMission.evidence_refs.includes('gate:Active asset coverage gate'));
assert.ok(catalogMission.evidence_refs.includes('gate:HC Group MOON gate'));
const operatorMission=x.missions.find(m=>m.mission_id==='mission-operator-visibility');
assert.ok(operatorMission);
assert.equal(operatorMission.state,'DONE');
assert.ok(operatorMission.checkpoint.includes('local-spool'));
assert.ok(operatorMission.evidence_refs.includes('gate:Durable worker heartbeat gate'));
assert.ok(operatorMission.evidence_refs.includes('gate:Durable evidence store gate'));
assert.ok(x.relationship_contract.goal_to_project);assert.ok(x.relationship_contract.mission_to_evidence);
assert.equal(x.worker_contract.ownership_until_terminal,true);
assert.deepEqual(new Set(x.worker_contract.terminal_states),new Set(['DONE','OWNER_REQUIRED','SAFETY_STOP','HARD_BLOCKED']));
assert.equal(x.worker_contract.no_green_without_evidence,true);
for(const k of ['mission_id','correlation_id','component','outcome']) assert.ok(x.evidence_contract.required_links.includes(k),k);
console.log(`LIFECYCLE_REGISTRY_PASS goals=${x.goals.length} missions=${x.missions.length} relations=project,system,tool,evidence worker_contract=1 evidence_links=1`);
