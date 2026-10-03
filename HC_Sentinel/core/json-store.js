import {mkdir,readFile,writeFile,rename} from "node:fs/promises";
import {dirname} from "node:path";
export class JsonStore {
  constructor(file){this.file=file;}
  async read(fallback={}){try{return JSON.parse(await readFile(this.file,"utf8"));}catch(e){if(e.code==="ENOENT") return fallback;throw e;}}
  async write(data){
    await mkdir(dirname(this.file),{recursive:true});
    const tmp=this.file+".tmp";
    await writeFile(tmp,JSON.stringify(data,null,2),"utf8");
    await rename(tmp,this.file);
    return data;
  }
  async update(mutator,fallback={}){
    const current=await this.read(fallback);const next=await mutator(structuredClone(current));
    return this.write(next);
  }
}
