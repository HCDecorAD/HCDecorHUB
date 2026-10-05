import assert from "node:assert/strict";
import {selectTransport, failover, markHealthy} from "./router.mjs";

const config={adapters:[
{id:"local-direct",priority:10},{id:"native-connector",priority:20},{id:"hcdr",priority:30},{id:"rdc",priority:90}
]};
let state={adapters:{},mission:"M1",checkpoint:"CP7"};
state=markHealthy(config,state,"local-direct","gateway-http-200");
state=markHealthy(config,state,"hcdr","relay-ok");
state.adapters["native-connector"]={health:"unavailable"};
assert.equal(selectTransport(config,state).id,"local-direct");
state=failover(config,state,"local-direct","offline");
assert.equal(state.active,"hcdr");
assert.equal(state.mission,"M1");
assert.equal(state.checkpoint,"CP7");
state=failover(config,state,"hcdr","blocked");
state.adapters["rdc"]={health:"healthy"};
assert.equal(selectTransport(config,state).id,"rdc");
console.log("IMASTER_TRANSPORT_MESH_SMOKE_PASS");
