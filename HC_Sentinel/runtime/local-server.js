import http from "node:http";
import {readFile} from "node:fs/promises";
import {extname,resolve,sep} from "node:path";
import {fileURLToPath} from "node:url";

const MIME={".html":"text/html; charset=utf-8",".js":"text/javascript; charset=utf-8",".css":"text/css; charset=utf-8",".json":"application/json; charset=utf-8"};
async function readBody(req){let body="";for await(const chunk of req) body+=chunk;return body?JSON.parse(body):{};}
const send=(res,data,code=200)=>{res.statusCode=code;res.setHeader("content-type",MIME[".json"]);res.end(JSON.stringify(data));};

export function createLocalServer({controller,status=()=>({status:"READY"}),evidence=null,settings=null,findings=null,logs=null,targetStore=null,projects=null,repairQueue=null,watchStore=null,watchEngine=null,windowProvider=null,chatBridge=null,tray=null,stateBackup=null,uiDir=null}){
  const uiRoot=uiDir?resolve(fileURLToPath(uiDir)):null;
  return http.createServer(async(req,res)=>{
    const url=new URL(req.url,"http://127.0.0.1");
    if(req.method==="GET"&&url.pathname==="/api/status"){send(res,status());return;}
    if(req.method==="POST"&&url.pathname==="/api/tray-test"){
      try{const out=tray?await tray.send({title:"HC Sentinel",message:"Tray notification test",level:"info"}):{delivered:false};await logs?.append?.({level:"info",type:"TRAY_TEST",message:"Tray notification test"});send(res,{status:out.delivered?"DELIVERED":"UNAVAILABLE",...out});}
      catch(e){send(res,{status:"BLOCKED",error:String(e.message||e)},400);}return;
    }
    if(req.method==="POST"&&url.pathname==="/api/backup-state"){
      try{if(!stateBackup)throw new Error("BACKUP_UNAVAILABLE");const name=`ui-${Date.now()}`;const path=await stateBackup.create(name);await logs?.append?.({level:"info",type:"STATE_BACKUP",message:name});send(res,{status:"BACKED_UP",name,path});}
      catch(e){send(res,{status:"BLOCKED",error:String(e.message||e)},400);}return;
    }
    if(req.method==="POST"&&url.pathname==="/api/command"){
      try{const data=await readBody(req);await logs?.append?.({level:"info",type:"COMMAND",message:String(data.command??"")});send(res,await controller.run(data.command));}
      catch(e){await logs?.append?.({level:"error",type:"COMMAND_ERROR",message:String(e.message||e)});send(res,{status:"BLOCKED",error:String(e.message||e)},400);}return;
    }
    if(req.method==="POST"&&url.pathname==="/api/action"){
      try{const data=await readBody(req);send(res,await controller.action(data.action,data.command));}
      catch(e){send(res,{status:"BLOCKED",error:String(e.message||e)},400);}return;
    }
    if(url.pathname==="/api/projects"&&targetStore){
      try{
        if(req.method==="GET"){send(res,{items:await targetStore.list()});return;}
        if(req.method==="POST"){
          const target=await targetStore.add(await readBody(req));
          if(!projects.get(target.projectId))projects.register({id:target.projectId,name:target.name??target.projectId.toUpperCase()});
          await logs?.append?.({level:"info",type:"PROJECT_ADDED",message:`${target.projectId} ${target.url}`});
          send(res,{status:"ADDED",target});return;
        }
      }catch(e){send(res,{status:"BLOCKED",error:String(e.message||e)},400);return;}
    }
    if(req.method==="GET"&&url.pathname==="/api/repairs"){send(res,{items:repairQueue?await repairQueue.list():[]});return;}
    if(req.method==="GET"&&url.pathname==="/api/windows"){try{send(res,{items:windowProvider?await windowProvider.list():[]});}catch(e){send(res,{items:[],error:String(e.message||e)},500);}return;}
    if(req.method==="GET"&&url.pathname==="/api/chat-targets"){try{send(res,chatBridge?await chatBridge.list():{items:[]});}catch(e){send(res,{items:[],error:String(e.message||e)},500);}return;}
    if(url.pathname==="/api/watchers"&&watchStore){
      try{
        if(req.method==="GET"){send(res,{items:await watchStore.list()});return;}
        if(req.method==="POST"){
          const data=await readBody(req);const id=data.id??`watch-${Date.now()}`;
          await watchStore.upsert({...data,id});send(res,{status:"SAVED",watch:(await watchStore.list()).find(x=>x.id===id)});return;
        }
      }catch(e){send(res,{status:"BLOCKED",error:String(e.message||e)},400);return;}
    }
    const wm=url.pathname.match(/^\/api\/watchers\/([^/]+)\/(start|stop|tick|remove)$/);
    if(wm&&watchStore&&watchEngine){
      const id=decodeURIComponent(wm[1]),op=wm[2];
      try{
        if(op==="start"){send(res,{status:"STARTED",watch:await watchStore.patch(id,{enabled:true,runtime:{state:"ARMED"}})});return;}
        if(op==="stop"){send(res,{status:"STOPPED",watch:await watchStore.patch(id,{enabled:false,runtime:{...(await watchStore.list()).find(x=>x.id===id)?.runtime,state:"STOPPED"}})});return;}
        if(op==="remove"){await watchStore.remove(id);send(res,{status:"REMOVED",id});return;}
        if(op==="tick"){
          const w=(await watchStore.list()).find(x=>x.id===id);if(!w)throw new Error("WATCH_NOT_FOUND");
          const result=await watchEngine.tickOne({...w,intervalSec:0});send(res,{status:"TICKED",result});return;
        }
      }catch(e){send(res,{status:"BLOCKED",error:String(e.message||e)},400);return;}
    }
    if(req.method==="GET"&&url.pathname==="/api/evidence"){send(res,{items:evidence?await evidence.list({projectId:url.searchParams.get("projectId")||undefined,type:url.searchParams.get("type")||undefined}):[]});return;}
    if(req.method==="GET"&&url.pathname==="/api/findings"){send(res,{items:findings?await findings.list({state:url.searchParams.get("state")||undefined,projectId:url.searchParams.get("projectId")||undefined}):[]});return;}
    if(req.method==="GET"&&url.pathname==="/api/logs"){send(res,{items:logs?await logs.list({level:url.searchParams.get("level")||undefined,limit:url.searchParams.get("limit")||100}):[]});return;}
    if(url.pathname==="/api/settings"&&settings){
      if(req.method==="GET"){send(res,await settings.get());return;}
      if(req.method==="POST"){send(res,await settings.patch(await readBody(req)));return;}
    }
    if(req.method==="GET"&&uiRoot){
      const rel=decodeURIComponent(url.pathname==="/"?"/index.html":url.pathname).replace(/^\/+/, "");
      if(rel.split(/[\\/]/).includes("..")){res.statusCode=403;res.end("Forbidden");return;}
      const file=resolve(uiRoot,rel);
      if(file!==uiRoot&&!file.startsWith(uiRoot+sep)){res.statusCode=403;res.end("Forbidden");return;}
      try{const data=await readFile(file);res.setHeader("content-type",MIME[extname(file)]||"application/octet-stream");res.end(data);return;}catch{}
    }
    send(res,{error:"NOT_FOUND"},404);
  });
}
