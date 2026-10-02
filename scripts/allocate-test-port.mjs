import net from 'node:net';

export async function canBind(port, host='127.0.0.1'){
  return await new Promise(resolve=>{
    const server=net.createServer();
    server.unref();
    server.once('error',()=>resolve(false));
    server.listen({port,host,exclusive:true},()=>server.close(()=>resolve(true)));
  });
}

export async function findFreePort(start=3219,end=3299,host='127.0.0.1'){
  for(let port=start;port<=end;port++) if(await canBind(port,host)) return port;
  throw new Error(`NO_FREE_PORT ${start}-${end}`);
}

if(import.meta.url===new URL(`file://${process.argv[1]}`).href){
  const start=Number(process.env.HC_PORT_START||process.argv[2]||3219);
  const end=Number(process.env.HC_PORT_END||process.argv[3]||3299);
  const port=await findFreePort(start,end);
  process.stdout.write(String(port));
}
