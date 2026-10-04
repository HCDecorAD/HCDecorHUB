import {readdir,stat,rm} from "node:fs/promises";import {join} from "node:path";
export async function enforceRetention(dir,{maxAgeMs,maxFiles,now=()=>Date.now()}={}){
  let names=[];try{names=await readdir(dir);}catch(e){if(e.code==="ENOENT")return {deleted:[]};throw e;}
  const items=[];
  for(const name of names){const path=join(dir,name);const s=await stat(path);if(s.isFile())items.push({name,path,mtimeMs:s.mtimeMs});}
  items.sort((a,b)=>b.mtimeMs-a.mtimeMs);
  const del=new Set();
  if(Number.isFinite(maxAgeMs))for(const x of items)if(now()-x.mtimeMs>maxAgeMs)del.add(x.path);
  if(Number.isFinite(maxFiles)&&maxFiles>=0)for(const x of items.slice(maxFiles))del.add(x.path);
  for(const path of del)await rm(path,{force:true});
  return {deleted:[...del]};
}
