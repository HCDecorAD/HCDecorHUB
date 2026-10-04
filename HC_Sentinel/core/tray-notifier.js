import {mkdir,appendFile} from "node:fs/promises";import {dirname} from "node:path";
export class TrayNotifier{
  constructor(file){this.file=file;}
  async send({title="HC Sentinel",message="Update",level="info"}={}){
    await mkdir(dirname(this.file),{recursive:true});
    await appendFile(this.file,JSON.stringify({at:new Date().toISOString(),title,message,level})+"\n","utf8");
    return {delivered:true};
  }
}
