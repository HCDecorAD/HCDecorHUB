import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';
import {createFileDurableAdapter} from '../lib/durable-file-adapter.mjs';

const dir=await fs.mkdtemp(path.join(os.tmpdir(),'hc-durable-adapter-'));
const p=createFileDurableAdapter(dir);
let pass=0;const t=async(name,fn)=>{await fn();pass++;console.log('PASS',name)};

await t('local adapter is durable but not production authority',async()=>{
 assert.equal(p.available,true);assert.equal(p.productionAuthority,false);assert.equal(p.name,'file-durable-local');
});
await t('put/get survives new adapter instance',async()=>{
 await p.put('mission',{state:'READY',checkpoint:1});
 const p2=createFileDurableAdapter(dir);
 assert.deepEqual(await p2.get('mission'),{state:'READY',checkpoint:1});
});
await t('transaction commits atomically as one state replacement',async()=>{
 await p.transaction(tx=>{tx.put('a',1);tx.put('b',2);return 'ok'});
 const p2=createFileDurableAdapter(dir);
 assert.equal(await p2.get('a'),1);assert.equal(await p2.get('b'),2);
});
await t('failed transaction leaves prior state intact',async()=>{
 await assert.rejects(()=>p.transaction(tx=>{tx.put('a',99);throw new Error('abort')}),/abort/);
 const p2=createFileDurableAdapter(dir);
 assert.equal(await p2.get('a'),1);
});
await t('audit append is durable JSONL',async()=>{
 await p.appendAudit({mission_id:'m1',event:'DONE'});
 const text=await fs.readFile(p.paths.auditFile,'utf8');
 const rows=text.trim().split('\n').map(JSON.parse);
 assert.equal(rows.at(-1).mission_id,'m1');assert.equal(rows.at(-1).event,'DONE');
});
await t('concurrent writes serialize without lost update',async()=>{
 await Promise.all([p.put('x',1),p.put('y',2),p.put('z',3)]);
 const p2=createFileDurableAdapter(dir);
 assert.equal(await p2.get('x'),1);assert.equal(await p2.get('y'),2);assert.equal(await p2.get('z'),3);
});
console.log(`DURABLE_FILE_ADAPTER_PASS ${pass}/6 production_authority=0 atomic_replace=1 audit_fsync=1`);
await fs.rm(dir,{recursive:true,force:true});
