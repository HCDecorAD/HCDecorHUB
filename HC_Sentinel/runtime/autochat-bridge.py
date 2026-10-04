import sys,json,hashlib
from pathlib import Path
AUTO=Path(r"D:\HCDecorHUB\HC_AutoChat")
sys.path.insert(0,str(AUTO))
from src.adapters.cdp import CDPDiscovery
from src.adapters.chatgpt_dom import ChatGPTDOM
from src.core.live_transaction import LiveTransaction
from src.adapters.cdp_rpc import CDPRPC
REG=AUTO/"data"/"chats.json"
STOP=AUTO/"runtime"/"STOP_ALL.flag"

def registry():
    try:return json.loads(REG.read_text(encoding="utf-8")).get("chats",{})
    except Exception:return {}

def exact(alias):
    x=registry().get(alias.upper())
    if not x or not x.get("conversation_id"): raise RuntimeError("CHAT_ALIAS_NOT_REGISTERED")
    if not x.get("enabled",True): raise RuntimeError("CHAT_ALIAS_DISABLED")
    return x

def pages():
    return {p.get("conversation_id"):p for p in CDPDiscovery().pages() if p.get("conversation_id")}

def busy(page):
    r=CDPRPC(page["websocket"])
    try:
        x=r.call("Runtime.evaluate",{"expression":r'''(()=>!!document.querySelector('button[data-testid="stop-button"],button[aria-label*="Dừng"],button[aria-label*="Ngừng"],button[aria-label*="Stop"]'))()''',"returnByValue":True})
        return bool(x.get("result",{}).get("value"))
    finally:r.close()

def assistant_tail(page):
    r=CDPRPC(page["websocket"])
    try:
        x=r.call("Runtime.evaluate",{"expression":r'''(()=>{const xs=[...document.querySelectorAll('[data-message-author-role="assistant"]')];return xs.length?(xs.at(-1).innerText||""):""})()''',"returnByValue":True})
        return str(x.get("result",{}).get("value") or "")
    finally:r.close()

def list_aliases():
    out=[]
    for k,v in registry().items():
        out.append({"alias":k,"conversation_id":v.get("conversation_id",""),"title":v.get("title",""),"enabled":bool(v.get("enabled",True))})
    return {"items":out}

def snapshot(alias):
    x=exact(alias);cid=x["conversation_id"];p=pages().get(cid)
    if not p:return {"online":False,"busy":False,"alias":alias.upper(),"conversation_id":cid,"state":"OFFLINE"}
    tail=assistant_tail(p); b=busy(p)
    return {"online":True,"busy":b,"alias":alias.upper(),"conversation_id":cid,"state":"BUSY" if b else "IDLE","title":p.get("title",""),"assistant_tail":tail[-12000:],"hash":hashlib.sha256(tail.encode("utf-8")).hexdigest()}

def send(alias,command):
    if STOP.exists():raise RuntimeError("STOP_ALL_ACTIVE")
    if not command.strip():raise RuntimeError("COMMAND_REQUIRED")
    x=exact(alias);cid=x["conversation_id"];p=pages().get(cid)
    if not p:raise RuntimeError("EXACT_TARGET_OFFLINE")
    if busy(p):return {"status":"BUSY","sent":False,"alias":alias.upper(),"conversation_id":cid}
    before=assistant_tail(p)
    result=LiveTransaction(ChatGPTDOM()).execute(p,cid,command)
    return {"status":"SENT","sent":True,"alias":alias.upper(),"conversation_id":cid,"command_sha256":hashlib.sha256(command.encode("utf-8")).hexdigest(),"before_hash":hashlib.sha256(before.encode("utf-8")).hexdigest(),"result":str(result)}

op=sys.argv[1] if len(sys.argv)>1 else "list"
try:
    if op=="list":out=list_aliases()
    elif op=="snapshot":out=snapshot(sys.argv[2])
    elif op=="send":out=send(sys.argv[2],sys.argv[3])
    else:raise RuntimeError("UNKNOWN_OPERATION")
    print(json.dumps(out,ensure_ascii=False))
except Exception as e:
    print(json.dumps({"status":"BLOCKED","error":str(e)},ensure_ascii=False))
    raise SystemExit(2)
