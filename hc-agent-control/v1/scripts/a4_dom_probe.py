import json,pathlib,sys
root=pathlib.Path(__file__).resolve().parents[1];sys.path.insert(0,str(root))
from src.adapters.cdp import CDPDiscovery
from src.adapters.chatgpt_dom import ChatGPTDOM
cid=sys.argv[1] if len(sys.argv)>1 else ""
pages=[p for p in CDPDiscovery().pages() if p.get("conversation_id")==cid]
if len(pages)!=1:print("A4_DOM_PROBE_BLOCKED exact conversation required");raise SystemExit(363)
s=ChatGPTDOM().snapshot(pages[0])
if s.get("conversation_id")!=cid:print("A4_DOM_PROBE_BLOCKED DOM identity mismatch");raise SystemExit(364)
(root/"logs").mkdir(exist_ok=True);(root/"logs"/"a4-dom-probe.json").write_text(json.dumps(s,ensure_ascii=False,indent=2),encoding="utf-8")
print("A4_DOM_PROBE_PASS",cid,s.get("user_message_count"))
