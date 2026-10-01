import json,time,pathlib
from src.adapters.desktop_scanner import edge_windows
from src.adapters.live_identity import selected_identities
ROOT=pathlib.Path(__file__).resolve().parents[2];OUT=ROOT/"runtime"/"supervisor-status.json";INTERVAL=120
ALLOW=("HC AutoDebug","HC Agent Control","HC AutoChat","HC Video Downloader","HC Insight","HC Design AI Studio")
def cycle():
 live=selected_identities();rows=[]
 for w in edge_windows():
  title=w.get("title","");managed=any(x.lower() in title.lower() for x in ALLOW)
  row={"source":"DESKTOP_UIA","pid":w["pid"],"hwnd":w["hwnd"],"title":title,"managed":managed}
  exact=next((x for x in live if x["window"]==title and x["confidence"]=="EXACT_SELECTED"),None)
  if not managed:row["action"]="IGNORE_NOT_ALLOWLISTED"
  elif exact:row.update({"selected_title":exact["selected_title"],"conversation_id":exact["conversation_id"],"url":exact["url"],"confidence":"EXACT_SELECTED","action":"EXACT_SELECTED_READY"})
  else:row["action"]="OBSERVE_ONLY_NO_EXACT_CID"
  rows.append(row)
 return rows
def main():
 OUT.parent.mkdir(parents=True,exist_ok=True)
 while True:
  try:data={"updated":time.time(),"mode":"ACTIVE_PATROL","interval_seconds":INTERVAL,"new_chat":"FORBIDDEN","send":"LOCKED","windows":cycle()}
  except Exception as e:data={"updated":time.time(),"mode":"ACTIVE_PATROL","error":str(e),"new_chat":"FORBIDDEN","send":"LOCKED"}
  OUT.write_text(json.dumps(data,ensure_ascii=False,indent=2),encoding="utf-8");time.sleep(INTERVAL)
if __name__=="__main__":main()
