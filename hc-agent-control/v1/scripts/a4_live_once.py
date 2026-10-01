import json,pathlib,sys
root=pathlib.Path(__file__).resolve().parents[1];sys.path.insert(0,str(root))
from src.adapters.cdp import CDPDiscovery
from src.adapters.chatgpt_dom import ChatGPTDOM
from src.core.live_transaction import LiveTransaction
tok=root/"runtime"/"G2_LIVE_ARM.json"
try:
 d=json.loads(tok.read_text(encoding="utf-8"));cid=d.get("conversation_id","");text=" ".join(sys.argv[1:]).strip()
 if d.get("scope")!="G2_SINGLE_COMMAND" or d.get("uses")!=1 or not cid or not text:raise RuntimeError("invalid one-shot arm")
 pages=[p for p in CDPDiscovery().pages() if p.get("conversation_id")==cid]
 if len(pages)!=1:raise RuntimeError("exact target unavailable or ambiguous")
 result=LiveTransaction(ChatGPTDOM()).execute(pages[0],cid,text)
 (root/"logs").mkdir(exist_ok=True);(root/"logs"/"a5-live-verify.json").write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding="utf-8")
 print("AUTOCHAT_A4_A5_LIVE_PASS",cid)
finally:
 try:tok.unlink()
 except FileNotFoundError:pass
