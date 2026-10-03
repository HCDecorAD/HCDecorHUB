import fs from 'node:fs';
import path from 'node:path';
import {validateEvidenceEvent} from '../evidence-envelope.mjs';

export class DurableEvidenceStore{
 constructor(file,{warn_bytes=5*1024*1024}={}){this.file=file;this.warn_bytes=warn_bytes;fs.mkdirSync(path.dirname(file),{recursive:true});if(!fs.existsSync(file))fs.writeFileSync(file,'');this.last_read={state:'HEALTHY',malformed_lines:0,total_lines:0,valid_events:0,bytes:0,needs_rotation:false};}
 append(event){const v=validateEvidenceEvent(event);if(!v.ok)throw new Error('INVALID_EVIDENCE_EVENT:'+v.error);fs.appendFileSync(this.file,JSON.stringify(event)+'\n');return event;}
 _read(){
  const lines=fs.readFileSync(this.file,'utf8').split(/\r?\n/).filter(Boolean);
  const rows=[];let malformed=0;
  for(const line of lines){try{rows.push(JSON.parse(line));}catch{malformed++;}}
  const bytes=fs.statSync(this.file).size;const needs_rotation=bytes>=this.warn_bytes;this.last_read={state:malformed?'DEGRADED':needs_rotation?'ATTENTION':'HEALTHY',malformed_lines:malformed,total_lines:lines.length,valid_events:rows.length,bytes,needs_rotation,warn_bytes:this.warn_bytes};
  return rows;
 }
 list({limit=50,mission_id=null,correlation_id=null}={}){
  return this._read().filter(e=>(!mission_id||e.mission_id===mission_id)&&(!correlation_id||e.correlation_id===correlation_id)).slice(-limit).reverse();
 }
 rotate({force=false}={}){
  const health=this.health();
  if(!force&&!health.needs_rotation)return {rotated:false,reason:'below_threshold',health};
  if(health.bytes===0)return {rotated:false,reason:'empty',health};
  const dir=path.dirname(this.file);const base=path.basename(this.file);const stamp=new Date().toISOString().replace(/[:.]/g,'-');
  const archive=path.join(dir,base+'.'+stamp+'.archive');
  fs.renameSync(this.file,archive);fs.writeFileSync(this.file,'');
  this.last_read={state:'HEALTHY',malformed_lines:0,total_lines:0,valid_events:0,bytes:0,needs_rotation:false,warn_bytes:this.warn_bytes};
  return {rotated:true,archive,previous:health,current:{...this.last_read}};
 }
 health(){this._read();return {...this.last_read};}
}
