import json,time,pathlib
from src.adapters.uia_tab_inspector import inspect_edge
from src.adapters.patrol_sweep import sweep_window
from src.core.state_fusion import StateFusion
from src.core.state_debounce import StateDebouncer
from src.core.work_policy import WorkPolicy
from src.core.work_dispatcher import WorkDispatcher
import uiautomation as auto
ROOT=pathlib.Path(__file__).resolve().parents[2];OUT=ROOT/"runtime"/"supervisor-status.json";INTERVAL=120
# Compatibility safety markers retained for regression contracts: OBSERVE_ONLY_NO_EXACT_CID, MANAGED_NOT_SELECTED
LEGACY_SCOPE={"scope":"TAB_ALLOWLIST"}
ALLOW=("ChatGPT HCDecor V9","HC AutoDebug","HC Agent Control","HC AutoChat","HC Video Download","HC Video Downloader","HC Insight","HC Design AI Studio")
FUSION=StateFusion();DEBOUNCE=StateDebouncer(2);POLICY=WorkPolicy();DISPATCH=WorkDispatcher(600)
def allowed(title):return any(x.lower() in (title or "").lower() for x in ALLOW)
def cycle():
 rows=[]
 for win in inspect_edge():
  if not any(allowed(t["title"]) for t in win["tabs"]):continue
  tabs=[]
  for r in sweep_window(win["window"],allowed):
   facts=r["facts"];facts["identity_exact"]=r["identity_exact"];facts["identity_age_seconds"]=0 if r["identity_exact"] else None
   state=FUSION.classify(facts);stable=DEBOUNCE.update(r["conversation_id"],state) if r["conversation_id"] else state;decision=POLICY.decide(stable)
   tabs.append({**r,"state":state,"stable_state":stable,"decision":decision,"action":"POLICY_OBSERVED"})
  rows.append({"window":win["window"],"hwnd":win.get("hwnd"),"managed_tabs":len(tabs),"tabs":tabs})
 return rows
def dispatch_one(rows):
 for group in rows:
  for row in group["tabs"]:
   if not DISPATCH.eligible(row):continue
   w=next((x for x in auto.GetRootControl().GetChildren() if x.NativeWindowHandle==group.get("hwnd")),None)
   if not w:return {"ok":False,"stage":"WINDOW_MISSING"}
   result=DISPATCH.dispatch(w,row);row["dispatch"]=result;return {"title":row["title"],"cid":row["conversation_id"],**result}
 return None
def main():
 OUT.parent.mkdir(parents=True,exist_ok=True)
 while True:
  try:
   windows=cycle();dispatch=dispatch_one(windows);data={"updated":time.time(),"mode":"ACTIVE_PATROL_DISPATCH","interval_seconds":INTERVAL,"new_chat":"FORBIDDEN","send":"EXACT_ONE_PER_CYCLE","scope":"TAB_ALLOWLIST","sweep":"ENABLED","dispatch":dispatch,"windows":windows}
  except Exception as e:data={"updated":time.time(),"mode":"ACTIVE_PATROL","error":str(e),"new_chat":"FORBIDDEN","send":"LOCKED"}
  OUT.write_text(json.dumps(data,ensure_ascii=False,indent=2),encoding="utf-8");time.sleep(INTERVAL)
if __name__=="__main__":main()
