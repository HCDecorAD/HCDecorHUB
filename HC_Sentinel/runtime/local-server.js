import http from "node:http";
import {readFile} from "node:fs/promises";
import {extname,join,normalize} from "node:path";
import {fileURLToPath} from "node:url";

const MIME={".html":"text/html; charset=utf-8",".js":"text/javascript; charset=utf-8",".css":"text/css; charset=utf-8",".json":"application/json; charset=utf-8"};

async function readBody(req){let body="";for await(const chunk of req) body+=chunk;return body?JSON.parse(body):{};}

export function createLocalServer({controller,status=()=>({status:"READY"}),evidence=null,settings=null,uiDir=null}){
  const uiRoot=uiDir?fileURLToPath(uiDir):null;
  return http.createServer(async(req,res)=>{
    const url=new URL(req.url,"http://127.0.0.1");

    if(req.method==="GET"&&url.pathname==="/api/status"){
      res.setHeader("content-type",MIME[".json"]);res.end(JSON.stringify(status()));return;
    }

    if(req.method==="POST"&&url.pathname==="/api/command"){
      res.setHeader("content-type",MIME[".json"]);
      try{const data=await readBody(req);const result=await controller.run(data.command);res.statusCode=200;res.end(JSON.stringify(result));}
      catch(e){res.statusCode=400;res.end(JSON.stringify({status:"BLOCKED",error:String(e.message||e)}));}
      return;
    }

    if(req.method==="GET"&&url.pathname==="/api/evidence"){
      res.setHeader("content-type",MIME[".json"]);
      if(!evidence){res.end(JSON.stringify({items:[]}));return;}
      const items=await evidence.list({projectId:url.searchParams.get("projectId")||undefined,type:url.searchParams.get("type")||undefined});
      res.end(JSON.stringify({items}));return;
    }

    if(url.pathname==="/api/settings"&&settings){
      res.setHeader("content-type",MIME[".json"]);
      if(req.method==="GET"){res.end(JSON.stringify(await settings.get()));return;}
      if(req.method==="POST"){res.end(JSON.stringify(await settings.patch(await readBody(req))));return;}
    }

    if(req.method==="GET"&&uiRoot){
      const rel=url.pathname==="/"?"/index.html":url.pathname;
      const safe=normalize(rel).replace(/^(..(/|\|$))+/, "");
      const file=join(uiRoot,safe);
      if(!file.startsWith(uiRoot)){res.statusCode=403;res.end("Forbidden");return;}
      try{
        const data=await readFile(file);
        res.setHeader("content-type",MIME[extname(file)]||"application/octet-stream");
        res.end(data);return;
      }catch{}
    }

    res.statusCode=404;res.setHeader("content-type",MIME[".json"]);res.end(JSON.stringify({error:"NOT_FOUND"}));
  });
}
