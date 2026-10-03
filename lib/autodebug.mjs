import fs from 'node:fs';

export function loadFixMemory(path='config/fix-memory.json'){
  const m=JSON.parse(fs.readFileSync(path,'utf8'));
  if(m?.schema_version!=='1.0.0'||!Array.isArray(m.fixes)) throw new Error('invalid_fix_memory');
  return m;
}
export function diagnoseFailure({fingerprint,component,symptom},memory=loadFixMemory()){
  if(!fingerprint||typeof fingerprint!=='string') return {matched:false,reason:'fingerprint_required'};
  const hit=memory.fixes.find(x=>x.fingerprint===fingerprint);
  if(!hit) return {matched:false,fingerprint,reason:'no_verified_fix'};
  if(component&&hit.component!==component) return {matched:false,fingerprint,reason:'component_mismatch'};
  if(symptom&&hit.symptom!==symptom) return {matched:false,fingerprint,reason:'symptom_mismatch'};
  return {matched:true,fingerprint,component:hit.component,diagnosis:hit.symptom,repair_plan:hit.fix,verified_commits:[...hit.verified_commits],evidence_ref:hit.evidence};
}
