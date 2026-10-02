import fs from 'node:fs';
import path from 'node:path';
import {validateEvidenceEvent} from '../evidence-envelope.mjs';

export class DurableEvidenceStore{
 constructor(file){this.file=file;fs.mkdirSync(path.dirname(file),{recursive:true});if(!fs.existsSync(file))fs.writeFileSync(file,'');this.last_read={state:'HEALTHY',malformed_lines:0,total_lines:0,valid_events:0};}
 append(event){const v=validateEvidenceEvent(event);if(!v.ok)throw new Error('INVALID_EVIDENCE_EVENT:'+v.error);fs.appendFileSync(this.file,JSON.stringify(event)+'\n');return event;}
 _read(){
  const lines=fs.readFileSync(this.file,'utf8').split(/\r?\n/).filter(Boolean);
  const rows=[];let malformed=0;
  for(const line of lines){try{rows.push(JSON.parse(line));}catch{malformed++;}}
  this.last_read={state:malformed?'DEGRADED':'HEALTHY',malformed_lines:malformed,total_lines:lines.length,valid_events:rows.length};
  return rows;
 }
 list({limit=50,mission_id=null,correlation_id=null}={}){
  return this._read().filter(e=>(!mission_id||e.mission_id===mission_id)&&(!correlation_id||e.correlation_id===correlation_id)).slice(-limit).reverse();
 }
 health(){this._read();return {...this.last_read};}
}
