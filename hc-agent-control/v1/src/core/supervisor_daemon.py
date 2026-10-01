import json,time,pathlib
from src.adapters.cdp import CDPDiscovery
from src.adapters.cdp_rpc import CDPRPC
from src.adapters.chatgpt_dom import ChatGPTDOM
from src.core.live_transaction import LiveTransaction
ROOT=pathlib.Path(__file__).resolve().parents[2];OUT=ROOT/"runtime"/"supervisor-status.json";STATE=ROOT/"runtime"/"supervisor-state.json"
INTERVAL=120
PROMPT="Tiếp tục các bước tiếp theo từ trạng thái hiện tại. Không làm lại phần đã PASS. Nếu một luồng bị chặn, tiếp tục luồng độc lập khác. Tự test, sửa và checkpoint khi GREEN."
JS=r'''(()=>{const c=document.querySelector("#prompt-textarea, div[role='textbox'][contenteditable='true']");const stop=[...document.querySelectorAll("button")].some(b=>/stop/i.test((b.getAttribute("aria-label")||"")+" "+(b.innerText||""))&&(!b.disabled));return {composer:!!c,stop_button:stop,title:document.title,url:location.href}})()'''
def inspect(p):
 rpc=CDPRPC(p["websocket"])
 try:return rpc.call("Runtime.evaluate",{"expression":JS,"returnByValue":True}).get("result",{}).get("value",{})
 finally:rpc.close()
def load_state():
 try:return json.loads(STATE.read_text(encoding="utf-8"))
 except Exception:return {}
def save_state(s):STATE.write_text(json.dumps(s,ensure_ascii=False,indent=2),encoding="utf-8")
def cycle():
 st=load_state();out=[];now=time.time()
 for p in CDPDiscovery().pages():
  cid=p.get("conversation_id")
  if not cid:continue
  row={"title":p.get("title"),"conversation_id":cid}
  try:
   s=inspect(p)
   if s.get("stop_button"):row["state"]="WORKING";row["action"]="SKIP"
   elif not s.get("composer"):row["state"]="UNKNOWN";row["action"]="SKIP"
   elif now-float(st.get(cid,0))<INTERVAL:row["state"]="READY";row["action"]="COOLDOWN"
   else:
    row["state"]="READY"
    v=LiveTransaction(ChatGPTDOM()).execute(p,cid,PROMPT,timeout=15)
    if not v.get("ok"):raise RuntimeError("postverify failed")
    st[cid]=now;save_state(st);row["action"]="CONTINUE_VERIFIED"
  except Exception as e:row["action"]="REVIEW_REQUIRED";row["error"]=str(e)
  out.append(row)
 return out
def main():
 OUT.parent.mkdir(parents=True,exist_ok=True)
 while True:
  try:OUT.write_text(json.dumps({"updated":time.time(),"mode":"ACTIVE","interval_seconds":INTERVAL,"chats":cycle()},ensure_ascii=False,indent=2),encoding="utf-8")
  except Exception as e:OUT.write_text(json.dumps({"updated":time.time(),"mode":"ACTIVE","error":str(e)},ensure_ascii=False),encoding="utf-8")
  time.sleep(INTERVAL)
if __name__=="__main__":main()
