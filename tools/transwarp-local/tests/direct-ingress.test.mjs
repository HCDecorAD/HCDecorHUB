import test from 'node:test';
import assert from 'node:assert/strict';
function validate(job){
  if(!job||job.schema!=='transwarp-local/job-v1')return'SCHEMA_INVALID';
  if(job.approved!==true)return'JOB_NOT_APPROVED';
  if(!/^[a-zA-Z0-9._-]{3,120}$/.test(job.job_id||''))return'JOB_ID_INVALID';
  if(!['process','powershell_file'].includes(job.action))return'ACTION_NOT_ALLOWED';
  if(typeof job.cwd!=='string'||!/^D:\\(HCDecorHUB|HC_DATA)\\/i.test(job.cwd))return'CWD_NOT_ALLOWED';
  return null;
}
test('accepts valid local manifest',()=>assert.equal(validate({schema:'transwarp-local/job-v1',approved:true,job_id:'job-1',action:'process',cwd:'D:\\HCDecorHUB\\x'}),null));
test('rejects unapproved job',()=>assert.equal(validate({schema:'transwarp-local/job-v1',approved:false,job_id:'job-1',action:'process',cwd:'D:\\HCDecorHUB\\x'}),'JOB_NOT_APPROVED'));
test('rejects path outside local roots',()=>assert.equal(validate({schema:'transwarp-local/job-v1',approved:true,job_id:'job-1',action:'process',cwd:'C:\\Temp'}),'CWD_NOT_ALLOWED'));
