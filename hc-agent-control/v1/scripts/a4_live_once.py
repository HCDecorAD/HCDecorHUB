import json,pathlib,sys,hashlib,datetime
root=pathlib.Path(__file__).resolve().parents[1];sys.path.insert(0,str(root))
from src.adapters.cdp import CDPDiscovery
from src.adapters.chatgpt_dom import ChatGPTDOM
from src.core.live_transaction import LiveTransaction
tok=root/"runtime"/"G2_LIVE_ARM.json";d=json.loads(tok.read_text(encoding="utf-8"));cid=d.get("conversation_id","");text=" ".join(sys.argv[1:]).strip()
expected=hashlib.sha256(text.encode("utf-8")).hexdigest();now=datetime.datetime.now(datetime.timezone.utc);exp=datetime.datetime.fromisoformat(d.get("expires",""))
if d.get("scope")!="G2_SINGLE_COMMAND" or d.get("uses")!=1 or not cid or not text or d.get("command_sha256")!=expected or now>=exp:raise RuntimeError("invalid/expired one-shot arm")
pages=[p for p in CDPDiscovery().pages() if p.get("conversation_id")==cid]
if len(pages)!=1:raise RuntimeError("exact target unavailable or ambiguous")
tok.unlink();result=LiveTransaction(ChatGPTDOM()).execute(pages[0],cid,text)
rid=""
try:rid=json.loads((root/"runtime"/"acceptance-run.json").read_text(encoding="utf-8")).get("run_id","")
except Exception:pass
result["run_id"]=rid;result["ts"]=datetime.datetime.now(datetime.timezone.utc).isoformat()
logs=root/"logs";logs.mkdir(exist_ok=True);(logs/"a5-live-verify.json").write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding="utf-8")
hist=logs/"a5-live-verifies.json"
try:doc=json.loads(hist.read_text(encoding="utf-8"))
except Exception:doc={"schema":"hc-autochat-live-verifies/v1","run_id":rid,"results":[]}
if doc.get("run_id")!=rid:doc={"schema":"hc-autochat-live-verifies/v1","run_id":rid,"results":[]}
doc["results"].append(result);hist.write_text(json.dumps(doc,ensure_ascii=False,indent=2),encoding="utf-8")
print("AUTOCHAT_A4_A5_LIVE_PASS",cid)
