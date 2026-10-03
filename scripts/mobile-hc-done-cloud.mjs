import fs from 'node:fs';
import path from 'node:path';

const root=process.cwd();
const manifestPath=path.join(root,'config','HC-DONE-ALL.manifest.json');
const outDir=path.join(root,'.runtime','mobile-hc-done');
fs.mkdirSync(outDir,{recursive:true});

const manifest=JSON.parse(fs.readFileSync(manifestPath,'utf8'));
const env=(name,def='')=>String(process.env[name]??def);
const localSignal=env('HCDR_AVAILABLE','unknown').toLowerCase();
const forceLocalRefresh=env('HC_FORCE_LOCAL_REFRESH','0')==='1';
const canonicalDone=manifest.state==='DONE' && manifest.packages?.every(p=>p.state==='DONE');
const live=manifest.verification?.live_hcdr;
const reusableLocal=Boolean(live?.state==='SUCCESS' && live?.result?.includes('ok=true'));

let localState='WAITING_RESOURCE';
let localReason='HOCUONG availability not confirmed';
if(!forceLocalRefresh && reusableLocal){
  localState='REUSED_PASS';
  localReason='verified live HCDR evidence reused; no fresh local execution required';
}else if(['true','1','online'].includes(localSignal)){
  localState='READY_FOR_LOCAL_REFRESH';
  localReason='HOCUONG resource advertised online';
}else if(['false','0','offline'].includes(localSignal)){
  localState='WAITING_RESOURCE';
  localReason='HOCUONG offline; cloud lane continues and local lane is resumable';
}

const cloudState=canonicalDone?'DONE':'ACTION_REQUIRED';
const state=cloudState==='DONE' && ['REUSED_PASS','READY_FOR_LOCAL_REFRESH'].includes(localState)
  ? 'DONE'
  : cloudState==='DONE' && localState==='WAITING_RESOURCE'
    ? 'CLOUD_DONE_LOCAL_WAITING'
    : 'ACTION_REQUIRED';

const summary={
  schema:'hc-mobile-done-orchestrator/v2',
  state,
  cloud_lane:{
    state:cloudState,
    canonical_manifest:manifestPath.replaceAll('\\','/'),
    terminal_token:manifest.terminal_token||null,
    packages:manifest.packages||[]
  },
  local_lane:{
    state:localState,
    resource:'HOCUONG',
    force_refresh:forceLocalRefresh,
    last_verified_live_hcdr:live||null,
    reason:localReason,
    resume_policy:'AUTO_RESUME_WHEN_RESOURCE_AVAILABLE'
  },
  policy:{
    no_fake_green:true,
    reuse_verified_pass:true,
    wait_is_yield:true,
    blocked_local_does_not_block_cloud:true
  },
  next_phase: state==='DONE' ? 'REMOTE_SELF_HEALING' : (cloudState==='DONE'?'LOCAL_AUTO_RESUME':'CLOUD_REPAIR'),
  at:new Date().toISOString()
};
fs.writeFileSync(path.join(outDir,'cloud-summary.json'),JSON.stringify(summary,null,2)+'\n');
console.log(JSON.stringify(summary));
if(cloudState!=='DONE') process.exit(20);
process.exit(0);
