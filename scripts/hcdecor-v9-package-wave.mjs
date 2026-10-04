import fs from 'node:fs';
const m=JSON.parse(fs.readFileSync('config/hcdecor-v9-package-manifest.json','utf8'));
const done=new Set(m.packages.filter(x=>x.state==='DONE').map(x=>x.id));
const runnable=m.packages.filter(x=>['READY','BUILDING'].includes(x.state) && x.depends_on.every(d=>done.has(d)));
const waiting=m.packages.filter(x=>x.state==='WAITING_DEP');
console.log(JSON.stringify({program:m.program_id,status:m.status,runnable:runnable.map(x=>x.id),waiting:waiting.map(x=>({id:x.id,depends_on:x.depends_on.filter(d=>!done.has(d))}))},null,2));
