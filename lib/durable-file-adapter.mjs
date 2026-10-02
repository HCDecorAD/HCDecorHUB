import fs from 'node:fs/promises';
import path from 'node:path';
import crypto from 'node:crypto';

async function ensureDir(file){await fs.mkdir(path.dirname(file),{recursive:true})}
async function syncDir(dir){
  try{const h=await fs.open(dir,'r');try{await h.sync()}finally{await h.close()}}catch{}
}
async function atomicJsonWrite(file,value){
  await ensureDir(file);
  const tmp=path.join(path.dirname(file),'.'+path.basename(file)+'.tmp-'+process.pid+'-'+crypto.randomBytes(6).toString('hex'));
  const data=JSON.stringify(value,null,2)+'\n';
  let h;
  try{
    h=await fs.open(tmp,'wx');
    await h.writeFile(data,'utf8');
    await h.sync();
    await h.close(); h=null;
    await fs.rename(tmp,file);
    await syncDir(path.dirname(file));
  }catch(e){
    try{if(h)await h.close()}catch{}
    try{await fs.unlink(tmp)}catch{}
    throw e;
  }
}
async function appendJsonLine(file,value){
  await ensureDir(file);
  const h=await fs.open(file,'a');
  try{
    await h.writeFile(JSON.stringify(value)+'\n','utf8');
    await h.sync();
  }finally{await h.close()}
  await syncDir(path.dirname(file));
}

export function createFileDurableAdapter(root){
  const stateFile=path.join(root,'state.json');
  const auditFile=path.join(root,'audit.jsonl');
  let chain=Promise.resolve();
  const serialized=fn=>{
    const next=chain.then(fn,fn);
    chain=next.catch(()=>{});
    return next;
  };
  const readState=async()=>{
    try{return JSON.parse(await fs.readFile(stateFile,'utf8'))}catch(e){if(e?.code==='ENOENT')return {};throw e}
  };
  return {
    name:'file-durable-local',
    available:true,
    productionAuthority:false,
    async get(key){const s=await readState();return Object.prototype.hasOwnProperty.call(s,key)?s[key]:null},
    async put(key,value){
      return serialized(async()=>{const s=await readState();s[key]=value;await atomicJsonWrite(stateFile,s);return value});
    },
    async appendAudit(event){
      return serialized(async()=>{const row={timestamp:new Date().toISOString(),...event};await appendJsonLine(auditFile,row);return row});
    },
    async transaction(fn){
      return serialized(async()=>{
        const before=await readState();
        const draft=structuredClone(before);
        const result=await fn({
          get:key=>Object.prototype.hasOwnProperty.call(draft,key)?draft[key]:null,
          put:(key,value)=>{draft[key]=value;return value},
          delete:key=>delete draft[key]
        });
        await atomicJsonWrite(stateFile,draft);
        return result;
      });
    },
    paths:{stateFile,auditFile}
  };
}
