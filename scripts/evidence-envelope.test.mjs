import assert from 'node:assert/strict';
import {createEvidenceEvent,validateEvidenceEvent} from '../lib/evidence-envelope.mjs';

const e=createEvidenceEvent({mission_id:'m-1',event_type:'mission.checkpoint',component:'governor',outcome:'success',checkpoint:{stage:'S10'},attributes:{worker_id:'w1',token:'should-drop'}});
assert.equal(e.correlation_id,'m-1');
assert.equal(e.attributes.worker_id,'w1');
assert.equal('token' in e.attributes,false);
assert.equal(validateEvidenceEvent(e).ok,true);

const child=createEvidenceEvent({mission_id:'m-1',correlation_id:e.correlation_id,parent_event_id:e.event_id,event_type:'mission.done',component:'hc-done',outcome:'success'});
assert.equal(child.parent_event_id,e.event_id);
assert.equal(child.correlation_id,e.correlation_id);
assert.equal(validateEvidenceEvent(child).ok,true);

assert.throws(()=>createEvidenceEvent({event_type:'x',component:'y'}),/MISSION_ID_REQUIRED/);
assert.equal(validateEvidenceEvent({...e,outcome:'wat'}).ok,false);

console.log('EVIDENCE_ENVELOPE_PASS correlation=1 parent_link=1 secret_redaction=1 fail_closed=1');
