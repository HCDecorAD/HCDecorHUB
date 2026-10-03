import http from 'node:http';
import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

const host = process.env.TRANSWARP_INGRESS_HOST || '127.0.0.1';
const port = Number(process.env.TRANSWARP_INGRESS_PORT || 8787);
const root = process.env.TRANSWARP_DATA_ROOT || 'D:\\HC_DATA\\queue\\transwarp';
const inbox = path.join(root, 'inbox');
const secretFile = path.join(root, 'ingress.secret');

fs.mkdirSync(inbox, { recursive: true });
if (!fs.existsSync(secretFile)) fs.writeFileSync(secretFile, crypto.randomBytes(32).toString('hex'));
const secret = fs.readFileSync(secretFile, 'utf8').trim();

function reply(res, code, body) {
  res.writeHead(code, {'content-type':'application/json; charset=utf-8'});
  res.end(JSON.stringify(body));
}
function safeId(id='') {
  return /^[a-zA-Z0-9._-]{3,120}$/.test(id);
}
function validate(job) {
  if (!job || job.schema !== 'transwarp-local/job-v1') return 'SCHEMA_INVALID';
  if (job.approved !== true) return 'JOB_NOT_APPROVED';
  if (!safeId(job.job_id)) return 'JOB_ID_INVALID';
  if (!['process','powershell_file'].includes(job.action)) return 'ACTION_NOT_ALLOWED';
  if (typeof job.cwd !== 'string' || !/^D:\\(HCDecorHUB|HC_DATA)\\/i.test(job.cwd)) return 'CWD_NOT_ALLOWED';
  return null;
}

http.createServer((req,res)=>{
  if (req.method === 'GET' && req.url === '/health') return reply(res,200,{ok:true,service:'transwarp-local-ingress',mode:'LOCAL_FIRST'});
  if (req.method !== 'POST' || req.url !== '/jobs') return reply(res,404,{ok:false,error:'NOT_FOUND'});
  if ((req.headers['x-transwarp-token']||'') !== secret) return reply(res,401,{ok:false,error:'UNAUTHORIZED'});
  let body='';
  req.on('data',c=>{ body += c; if (body.length > 65536) req.destroy(); });
  req.on('end',()=>{
    try {
      const job = JSON.parse(body);
      const error = validate(job);
      if (error) return reply(res,400,{ok:false,error});
      const file = path.join(inbox, job.job_id + '.json');
      const tmp = file + '.tmp';
      fs.writeFileSync(tmp, JSON.stringify(job,null,2));
      fs.renameSync(tmp,file);
      reply(res,202,{ok:true,state:'ENQUEUED',job_id:job.job_id});
    } catch (e) {
      reply(res,400,{ok:false,error:'BAD_JSON'});
    }
  });
}).listen(port,host,()=>console.log(`TRANSWARP_INGRESS_READY http://${host}:${port}`));
