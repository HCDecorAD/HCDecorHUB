import assert from 'node:assert/strict';import fs from 'node:fs';import os from 'node:os';import path from 'node:path';
import {createEvidenceEvent} from '../lib/evidence-envelope.mjs';import {DurableEvidenceStore} from '../lib/governor/durable-evidence-store.mjs';
const dir=fs.mkdtempSync(path.join(os.tmpdir(),'hc-evidence-'));const file=path.join(dir,'events.jsonl');const s=new DurableEvidenceStore(file);
const a=createEvidenceEvent({mission_id:'m1',correlation_id:'c1',event_type:'gate',component:'quality',outcome:'success'});
const b=createEvidenceEvent({mission_id:'m2',event_type:'heartbeat',component:'worker',outcome:'info'});
s.append(a);s.append(b);const s2=new DurableEvidenceStore(file);assert.equal(s2.list().length,2);assert.equal(s2.list({mission_id:'m1'})[0].correlation_id,'c1');assert.equal(s2.list({correlation_id:'m2'})[0].mission_id,'m2');
assert.throws(()=>s.append({}),/INVALID_EVIDENCE_EVENT/);
fs.appendFileSync(file,'{malformed-json\n');
const safe=s2.list();
assert.equal(safe.length,2);
const health=s2.health();
assert.equal(health.state,'DEGRADED');
assert.equal(health.malformed_lines,1);
assert.equal(health.valid_events,2);
console.log('DURABLE_EVIDENCE_STORE_PASS durable=1 mission_filter=1 correlation_filter=1 fail_closed=1 malformed_tolerant=1 degraded_diagnostic=1');
const capFile=path.join(dir,'capacity.jsonl');const cap=new DurableEvidenceStore(capFile,{warn_bytes:1});cap.append(a);const capHealth=cap.health();assert.equal(capHealth.needs_rotation,true);assert.equal(capHealth.state,'ATTENTION');assert.ok(capHealth.bytes>=1);console.log('DURABLE_EVIDENCE_CAPACITY_DIAGNOSTIC_PASS attention=1 rotation_not_automatic=1');

const rotateFile=path.join(dir,'rotate.jsonl');const rot=new DurableEvidenceStore(rotateFile,{warn_bytes:1});rot.append(a);rot.append(b);const before=rot.health();assert.equal(before.needs_rotation,true);const rr=rot.rotate();assert.equal(rr.rotated,true);assert.ok(fs.existsSync(rr.archive));assert.equal(rot.list().length,0);const archived=fs.readFileSync(rr.archive,'utf8');assert.ok(archived.includes('"mission_id":"m1"'));assert.ok(archived.includes('"mission_id":"m2"'));assert.equal(rot.health().state,'HEALTHY');assert.equal(rot.rotate().reason,'below_threshold');console.log('DURABLE_EVIDENCE_ROTATION_PASS preserved_archive=1 fresh_spool=1 explicit_only=1');

const idxFile=path.join(dir,'indexed.jsonl');const idx=new DurableEvidenceStore(idxFile,{warn_bytes:1});idx.append(a);const ir=idx.rotate();assert.equal(ir.rotated,true);const archives=idx.archives();assert.equal(archives.length,1);assert.equal(archives[0].archive,ir.archive);assert.equal(archives[0].source_file,idxFile);assert.equal(archives[0].valid_events,1);assert.ok(Date.parse(archives[0].rotated_at));assert.ok(fs.existsSync(idx.archiveIndexFile()));console.log('DURABLE_EVIDENCE_ARCHIVE_INDEX_PASS indexed=1 traceable=1 append_only_manifest=1');
