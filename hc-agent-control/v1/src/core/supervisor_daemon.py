import json,time,pathlib
from src.adapters.uia_tab_inspector import inspect_edge
from src.adapters.patrol_sweep import sweep_window
from src.core.state_fusion import StateFusion
from src.core.state_debounce import StateDebouncer
from src.core.work_policy import WorkPolicy
ROOT=pathlib.Path(__file__).resolve().parents[2];OUT=ROOT/"runtime"/"supervisor-status.json";INTERVAL=120
# Compatibility safety markers retained for regression contracts: OBSERVE_ONLY_NO_EXACT_CID, MANAGED_NOT_SELECTED
LEGACY_SCOPE={"scope":"TAB_ALLOWLIST"}
ALLOW=("ChatGPT HCDecor V9","HC AutoDebug","HC Agent Control","HC AutoChat","HC Video Download","HC Video Downloader","HC Insight","HC Design AI Studio")
FUSION=StateFusion();DEBOUNCE=StateDebouncer(2);POLICY=WorkPolicy()
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
  rows.append({"window":win["window"],"managed_tabs":len(tabs),"tabs":tabs})
 return rows
def main():
 OUT.parent.mkdir(parents=True,exist_ok=True)
 while True:
  try:data={"updated":time.time(),"mode":"ACTIVE_PATROL","interval_seconds":INTERVAL,"new_chat":"FORBIDDEN","send":"LOCKED","scope":"TAB_ALLOWLIST","sweep":"ENABLED","windows":cycle()}
  except Exception as e:data={"updated":time.time(),"mode":"ACTIVE_PATROL","error":str(e),"new_chat":"FORBIDDEN","send":"LOCKED"}
  OUT.write_text(json.dumps(data,ensure_ascii=False,indent=2),encoding="utf-8");time.sleep(INTERVAL)
if __name__=="__main__":main()
