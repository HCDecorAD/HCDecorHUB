export class MaintenanceRunner{
  constructor({jobs=[]}={}){this.jobs=jobs;}
  async run(context={}){
    const results=[];
    for(const job of this.jobs){
      try{const value=await job.run(context);results.push({id:job.id,status:"PASS",value});}
      catch(e){results.push({id:job.id,status:"FAIL",error:String(e.message||e)});}
    }
    return {ok:results.every(x=>x.status==="PASS"),results};
  }
}
