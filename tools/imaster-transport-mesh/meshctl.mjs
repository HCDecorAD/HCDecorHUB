import {FileQueueAdapter} from "./adapters/file-queue.mjs";
import {envelope} from "./envelope.mjs";
const q=new FileQueueAdapter(process.env.IMASTER_MESH_QUEUE||"D:/HCDecorHUB/TransportMesh/queue");
const [action,...rest]=process.argv.slice(2);
if(!action){console.error("usage: node meshctl.mjs <action> [json]");process.exit(2)}
const payload=rest.length?JSON.parse(rest.join(" ")):{};
const req=envelope({missionId:process.env.IMASTER_MISSION_ID||"interactive",checkpoint:process.env.IMASTER_CHECKPOINT||null,action,payload});
console.log(JSON.stringify({ok:true,id:req.id,file:q.enqueue(req)}));
