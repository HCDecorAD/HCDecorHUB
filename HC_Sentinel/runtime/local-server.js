import http from "node:http";
export function createLocalServer({controller,status=()=>({status:"READY"})}){
  return http.createServer(async(req,res)=>{
    res.setHeader("content-type","application/json; charset=utf-8");
    if(req.method==="GET"&&req.url==="/api/status"){res.end(JSON.stringify(status()));return;}
    if(req.method==="POST"&&req.url==="/api/command"){
      let body="";for await(const chunk of req) body+=chunk;
      try{const data=JSON.parse(body||"{}");const result=await controller.run(data.command);res.statusCode=200;res.end(JSON.stringify(result));}
      catch(e){res.statusCode=400;res.end(JSON.stringify({status:"BLOCKED",error:String(e.message||e)}));}
      return;
    }
    res.statusCode=404;res.end(JSON.stringify({error:"NOT_FOUND"}));
  });
}
