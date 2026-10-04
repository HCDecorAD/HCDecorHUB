import fs from 'node:fs';
const m=JSON.parse(fs.readFileSync('config/hcdecor-v9-package-manifest.json','utf8'));
const done=new Set(m.packages.filter(x=>x.state==='DONE').map(x=>x.id));
const unresolved=x=>x.depends_on.filter(d=>!done.has(d));
const runnable=m.packages.filter(x=>['READY','BUILDING','WAITING_DEP'].includes(x.state) && unresolved(x).length===0);
const localPass=m.packages.filter(x=>x.state==='LOCAL_PASS');
const waiting=m.packages.filter(x=>x.state==='WAITING_DEP' && unresolved(x).length>0);
console.log(JSON.stringify({
  program:m.program_id,
  status:m.status,
  runnable:runnable.map(x=>x.id),
  local_pass_pending_ci:localPass.map(x=>x.id),
  waiting:waiting.map(x=>({id:x.id,depends_on:unresolved(x)}))
},null,2));
