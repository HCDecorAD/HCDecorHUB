import assert from 'node:assert/strict';import {AgentControlRuntime,createAutoChatBridge} from '../lib/agent-control-runtime.mjs';
const a=new AgentControlRuntime();const chat=createAutoChatBridge(a);chat.submit({mission_id:'m2',worker_id:'w2',correlation_id:'c2',text:'go'});
a.heartbeat({mission_id:'m2',checkpoint:'cpA',next_action:'continue'});
const before=chat.status('m2');const chat2=createAutoChatBridge(a);const after=chat2.status('m2');
assert.deepEqual(after,before);assert.equal(after.state,'RUNNING');assert.equal(after.checkpoint,'cpA');
console.log('HC_AUTOCHAT_RUNTIME_PASS chat_loss_does_not_cancel=1 operator_ui_only=1');
