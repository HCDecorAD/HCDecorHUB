import {createLocalServer} from "./local-server.js";
const controller={run:async command=>({status:"READY",command})};
const server=createLocalServer({controller,status:()=>({status:"SENTINEL_READY",version:"0.6.0"})});
const port=Number(process.env.HC_SENTINEL_PORT||43110);
server.listen(port,"127.0.0.1",()=>console.log(`HC Sentinel local server http://127.0.0.1:${port}`));
