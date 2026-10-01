import json,time,pathlib
from src.adapters.uia_tab_inspector import inspect_edge,clean_title
from src.adapters.live_identity import selected_identities
from src.adapters.uia_activity import selected_activity
from src.core.state_fusion import StateFusion
FUSION=StateFusion()
ROOT=pathlib.Path(__file__).resolve().parents[2];OUT=ROOT/"runtime"/"supervisor-status.json";INTERVAL=120
ALLOW=("ChatGPT HCDecor V9","HC AutoDebug","HC Agent Control","HC AutoChat","HC Video Download","HC Video Downloader","HC Insight","HC Design AI Studio")
def allowed(title):return any(x.lower() in (title or "").lower() for x in ALLOW)
def cycle():
 live=selected_identities();rows=[]
 for win in inspect_edge():
  exact=next((x for x in live if x["window"]==win["window"] and x["confidence"]=="EXACT_SELECTED"),None)
  tabs=[]
  for t in win["tabs"]:
   title=clean_title(t["title"]);managed=allowed(title);row={"title":title,"selected":t["selected"],"managed":managed}
   if not managed:row["action"]="IGNORE_NOT_ALLOWLISTED"
   elif t["selected"] and exact and clean_title(exact["selected_title"])==title:
    facts=selected_activity(win["window"]);facts["identity_exact"]=True;facts["identity_age_seconds"]=0
    state=FUSION.classify(facts);row.update({"conversation_id":exact["conversation_id"],"url":exact["url"],"confidence":"EXACT_SELECTED_SAME_TITLE","state":state,"facts":facts,"action":"STATE_OBSERVED"})
   elif t["selected"]:row["action"]="OBSERVE_ONLY_NO_EXACT_CID"
   else:row["action"]="MANAGED_NOT_SELECTED"
   tabs.append(row)
  rows.append({"window":win["window"],"managed_tabs":sum(1 for x in tabs if x["managed"]),"tabs":tabs})
 return rows
def main():
 OUT.parent.mkdir(parents=True,exist_ok=True)
 while True:
  try:data={"updated":time.time(),"mode":"ACTIVE_PATROL","interval_seconds":INTERVAL,"new_chat":"FORBIDDEN","send":"LOCKED","scope":"TAB_ALLOWLIST","windows":cycle()}
  except Exception as e:data={"updated":time.time(),"mode":"ACTIVE_PATROL","error":str(e),"new_chat":"FORBIDDEN","send":"LOCKED"}
  OUT.write_text(json.dumps(data,ensure_ascii=False,indent=2),encoding="utf-8");time.sleep(INTERVAL)
if __name__=="__main__":main()
