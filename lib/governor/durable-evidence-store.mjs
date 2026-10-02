import fs from 'node:fs';
import path from 'node:path';
import {validateEvidenceEvent} from '../evidence-envelope.mjs';

export class DurableEvidenceStore{
 constructor(file){this.file=file;fs.mkdirSync(path.dirname(file),{recursive:true});if(!fs.existsSync(file))fs.writeFileSync(file,'');}
 append(event){const v=validateEvidenceEvent(event);if(!v.ok)throw new Error('INVALID_EVIDENCE_EVENT:'+v.error);fs.appendFileSync(this.file,JSON.stringify(event)+'\n');return event;}
 list({limit=50,mission_id=null,correlation_id=null}={}){
  const rows=fs.readFileSync(this.file,'utf8').split(/\r?\n/).filter(Boolean).map(x=>JSON.parse(x));
  return rows.filter(e=>(!mission_id||e.mission_id===mission_id)&&(!correlation_id||e.correlation_id===correlation_id)).slice(-limit).reverse();
 }
}
