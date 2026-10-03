import {mkdir,cp,rm,rename,access} from "node:fs/promises";
import {dirname,join} from "node:path";

export class StateBackup{
  constructor({stateDir,backupDir}){this.stateDir=stateDir;this.backupDir=backupDir;}
  async create(name){
    if(!name||name.includes("..")||/[\\/]/.test(name)) throw new Error("INVALID_BACKUP_NAME");
    const dest=join(this.backupDir,name);
    await mkdir(this.backupDir,{recursive:true});
    await rm(dest,{recursive:true,force:true});
    await cp(this.stateDir,dest,{recursive:true,errorOnExist:false});
    return dest;
  }
  async restore(name){
    if(!name||name.includes("..")||/[\\/]/.test(name)) throw new Error("INVALID_BACKUP_NAME");
    const src=join(this.backupDir,name);await access(src);
    const parent=dirname(this.stateDir);await mkdir(parent,{recursive:true});
    const tmp=this.stateDir+".restore";
    await rm(tmp,{recursive:true,force:true});
    await cp(src,tmp,{recursive:true});
    await rm(this.stateDir,{recursive:true,force:true});
    await rename(tmp,this.stateDir);
    return this.stateDir;
  }
}
