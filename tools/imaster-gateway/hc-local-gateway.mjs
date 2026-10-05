import http from "node:http";
const HOST=process.env.HC_GATEWAY_HOST||"127.0.0.1", PORT=Number(process.env.HC_GATEWAY_PORT||8770);
const ZEUS=process.env.ZEUS_BASE||"http://127.0.0.1:8766", TOKEN=process.env.HC_GATEWAY_TOKEN||"", ZTOKEN=process.env.ZEUS_OWNER_TOKEN||"";
const reply=(r,c,b)=>{r.writeHead(c,{"content-type":"application/json","cache-control":"no-store"});r.end(JSON.stringify(b))};
async function read(req){let s="";for await(const x of req){s+=x;if(s.length>262144)throw Error("BODY_TOO_LARGE")}return s?JSON.parse(s):{}}
async function z(path,method="GET",payload){const h={"content-type":"application/json"};if(method!=="GET"){if(!ZTOKEN)throw Error("ZEUS_OWNER_TOKEN_REQUIRED");h["x-zeus-owner"]=ZTOKEN}const r=await fetch(ZEUS+path,{method,headers:h,body:payload?JSON.stringify(payload):undefined});const t=await r.text();let d;try{d=JSON.parse(t)}catch{d={raw:t}}return {status:r.status,ok:r.ok,data:d}}
const map={"/zeus/send":"/send","/zeus/create":"/create","/zeus/rename":"/rename"};
http.createServer(async(req,res)=>{try{
 if(req.method==="GET"&&req.url==="/health")return reply(res,200,{ok:true,id:"HC_LOCAL_GATEWAY",listen:HOST+":"+PORT});
 if(req.method==="GET"&&req.url==="/status"){const a=await z("/health");return reply(res,a.ok?200:503,{ok:a.ok,gateway:"UP",zeus:a.data})}
 if(req.method==="GET"&&req.url==="/tabs"){const a=await z("/tabs");return reply(res,a.status,{ok:a.ok,data:a.data})}
 if(req.method==="POST"&&map[req.url]){if(!TOKEN||req.headers["x-hc-gateway"]!==TOKEN)return reply(res,401,{ok:false,error:"UNAUTHORIZED"});const a=await z(map[req.url],"POST",await read(req));return reply(res,a.status,{ok:a.ok,data:a.data})}
 return reply(res,404,{ok:false,error:"NOT_FOUND"});
}catch(e){return reply(res,500,{ok:false,error:String(e.message||e)})}}).listen(PORT,HOST,()=>console.log(JSON.stringify({ok:true,id:"HC_LOCAL_GATEWAY",listen:HOST+":"+PORT,zeus:ZEUS})));
