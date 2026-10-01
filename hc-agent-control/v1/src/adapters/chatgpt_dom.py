from .cdp_rpc import CDPRPC
SNAPSHOT_JS=r'''(()=>{const m=location.pathname.match(/\/c\/([^/?#]+)/);const nodes=[...document.querySelectorAll('[data-message-author-role="user"]')];const last=nodes.length?nodes[nodes.length-1]:null;return JSON.stringify({conversation_id:m?m[1]:"",user_message_count:nodes.length,last_user_text:last?(last.innerText||last.textContent||"").trim():"",url:location.href,title:document.title});})()'''
class ChatGPTDOM:
 def snapshot(self,page):
  rpc=CDPRPC(page["websocket"])
  try:
   r=rpc.call("Runtime.evaluate",{"expression":SNAPSHOT_JS,"returnByValue":True})
   import json
   return json.loads(r["result"]["value"])
  finally:rpc.close()
 def composer_text(self,page):
  rpc=CDPRPC(page["websocket"])
  try:
   r=rpc.call("Runtime.evaluate",{"expression":"(()=>{const e=document.querySelector('#prompt-textarea');return e?((e.innerText||e.textContent||'').trim()):null})()","returnByValue":True})
   return r.get("result",{}).get("value")
  finally:rpc.close()
