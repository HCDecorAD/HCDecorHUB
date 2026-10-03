param()
$ErrorActionPreference="Stop"
$root="D:\HCDecorHUB\HC TransWarp Infrastructure"
Set-Location $root
New-Item -ItemType Directory -Force -Path "src\core","observability","workers","adapters","tests" | Out-Null

Set-Content "src\core\workflow.mjs" -Encoding utf8 -Value @'
export function nextReadyNodes(nodes=[]){
  const done=new Set(nodes.filter(n=>n.state==='DONE').map(n=>n.id));
  return nodes.filter(n=>n.state==='READY'&&(n.depends_on||[]).every(d=>done.has(d)));
}
export function markNode(nodes,id,state,evidence=null){
  return nodes.map(n=>n.id===id?{...n,state,evidence,updated_at:new Date().toISOString()}:n);
}
export function workflowDone(nodes=[]){return nodes.length>0&&nodes.every(n=>n.state==='DONE')}
'@

Set-Content "observability\evidence.mjs" -Encoding utf8 -Value @'
import fs from 'node:fs';
import path from 'node:path';
export class EvidenceStore{
  constructor(root){this.root=root;fs.mkdirSync(root,{recursive:true});this.file=path.join(root,'events.jsonl')}
  append(event){const e={ts:new Date().toISOString(),...event};fs.appendFileSync(this.file,JSON.stringify(e)+'\n');return e}
  read(){if(!fs.existsSync(this.file))return [];return fs.readFileSync(this.file,'utf8').split(/\r?\n/).filter(Boolean).map(x=>JSON.parse(x))}
  trace(correlationId){return this.read().filter(x=>x.correlation_id===correlationId)}
}
export function metricSnapshot({ready=0,waiting=0,running=0,done=0,failed=0}={}){
  return {ready,waiting,running,done,failed,total:ready+waiting+running+done+failed};
}
'@

Set-Content "workers\resource-router.mjs" -Encoding utf8 -Value @'
export function classifyResource(job={}){
  if(job.requires_gpu||job.requirements?.gpu)return 'gpu';
  if(job.requirements?.browser)return 'browser';
  if(job.requirements?.remote)return 'remote';
  return 'cpu';
}
export function gpuAdmission(job={},inventory=[]){
  const type=classifyResource(job);
  if(type!=='gpu')return {admitted:true,type};
  const node=inventory.filter(x=>x.status==='READY'&&x.gpu===true).sort((a,b)=>(a.load||0)-(b.load||0))[0];
  return node?{admitted:true,type,node_id:node.id}:{admitted:false,type,reason:'GPU_CAPACITY_UNAVAILABLE'};
}
'@

Set-Content "adapters\hcdr-fallback.mjs" -Encoding utf8 -Value @'
export function fallbackPlan({localAvailable=true,remoteAvailable=false,sideEffectState='NONE',checkpoint=null}={}){
  if(sideEffectState==='UNCERTAIN')return {action:'VERIFY_SIDE_EFFECT',transport:null,checkpoint};
  if(localAvailable)return {action:'RUN',transport:'LOCAL',checkpoint};
  if(remoteAvailable)return {action:'RUN',transport:'HCDR',checkpoint};
  return {action:'WAITING_RESOURCE',transport:null,checkpoint};
}
export function failoverAllowed(plan){return plan.action==='RUN'&&plan.transport==='HCDR'}
'@

Set-Content "tests\p7-p10.test.mjs" -Encoding utf8 -Value @'
import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {nextReadyNodes,markNode,workflowDone} from '../src/core/workflow.mjs';
import {EvidenceStore,metricSnapshot} from '../observability/evidence.mjs';
import {classifyResource,gpuAdmission} from '../workers/resource-router.mjs';
import {fallbackPlan,failoverAllowed} from '../adapters/hcdr-fallback.mjs';

test('P7 workflow dependency gate',()=>{
  let n=[{id:'a',state:'READY'},{id:'b',state:'READY',depends_on:['a']}];
  assert.deepEqual(nextReadyNodes(n).map(x=>x.id),['a']);
  n=markNode(n,'a','DONE');
  assert.deepEqual(nextReadyNodes(n).map(x=>x.id),['b']);
  n=markNode(n,'b','DONE');
  assert.equal(workflowDone(n),true);
});

test('P8 evidence trace continuity',()=>{
  const d=fs.mkdtempSync(path.join(os.tmpdir(),'twe-'));
  const s=new EvidenceStore(d);
  s.append({correlation_id:'c1',event:'start'});
  s.append({correlation_id:'c2',event:'x'});
  s.append({correlation_id:'c1',event:'done'});
  assert.equal(s.trace('c1').length,2);
  assert.equal(metricSnapshot({ready:1,done:2}).total,3);
  fs.rmSync(d,{recursive:true,force:true});
});

test('P9 GPU admission is demand-gated',()=>{
  assert.equal(classifyResource({requires_gpu:true}),'gpu');
  assert.equal(gpuAdmission({requires_gpu:true},[]).admitted,false);
  assert.equal(gpuAdmission({requires_gpu:true},[{id:'g1',gpu:true,status:'READY',load:0}]).node_id,'g1');
});

test('P10 HCDR fallback stays secondary',()=>{
  assert.equal(fallbackPlan({localAvailable:true,remoteAvailable:true}).transport,'LOCAL');
  const x=fallbackPlan({localAvailable:false,remoteAvailable:true,checkpoint:{stage:2}});
  assert.equal(failoverAllowed(x),true);
  assert.equal(x.checkpoint.stage,2);
  assert.equal(fallbackPlan({localAvailable:false,remoteAvailable:true,sideEffectState:'UNCERTAIN'}).action,'VERIFY_SIDE_EFFECT');
});
'@

node --test tests\p7-p10.test.mjs
if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}
npm test
if($LASTEXITCODE -ne 0){exit $LASTEXITCODE}

$cp=@(
'# HC TransWarp - CHECKPOINT LATEST',
'Date: 2026-10-03',
'Checkpoint: P1-P10',
'Status: DONE',
'',
'## PASS',
'- P1 Lane Manager',
'- P2 Ready/Wait Index',
'- P3 Fair Scheduler',
'- P4 Worker Pool',
'- P5 Durable Queue',
'- P6 Autoscale Adapter',
'- P7 Workflow Engine',
'- P8 Observability / Evidence',
'- P9 AI/GPU Resource Routing',
'- P10 HCDR / Remote Fallback',
'',
'## Evidence',
'- P7-P10 targeted suite PASS',
'- Full local regression PASS on HOCUONG',
'',
'## Runtime rule',
'Local-first. HCDR fallback only. WAIT/BLOCKED lane does not stall unrelated READY lanes.'
)
Set-Content "checkpoints\CHECKPOINT_LATEST.md" -Value $cp -Encoding utf8
Write-Output "TRANSWARP_P1_P10_FINAL_GATE_PASS"
