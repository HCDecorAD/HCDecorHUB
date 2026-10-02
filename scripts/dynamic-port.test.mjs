import assert from 'node:assert/strict';
import net from 'node:net';
import {findFreePort} from './allocate-test-port.mjs';

const blocker=net.createServer();
await new Promise((resolve,reject)=>{
  blocker.once('error',reject);
  blocker.listen({port:3219,host:'127.0.0.1',exclusive:true},resolve);
});
try{
  const port=await findFreePort(3219,3221);
  assert.notEqual(port,3219);
  assert.ok(port>=3220&&port<=3221);
  console.log('DYNAMIC_PORT_PASS occupied=3219 selected='+port);
}finally{
  await new Promise(resolve=>blocker.close(resolve));
}
