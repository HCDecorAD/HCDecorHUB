import json,time,pathlib
from src.adapters.desktop_scanner import edge_windows
from src.adapters.cdp import CDPDiscovery
ROOT=pathlib.Path(__file__).resolve().parents[2]
OUT=ROOT/"runtime"/"supervisor-status.json";INTERVAL=120
ALLOW=("HC AutoDebug","HC Agent Control","HC AutoChat","HC Video Downloader","HC Insight","HC Design AI Studio")
def cycle():
 desktop=edge_windows();cdp=CDPDiscovery().pages();rows=[]
 for w in desktop:
  title=w.get("title","");managed=any(x.lower() in title.lower() for x in ALLOW)
  row={"source":"DESKTOP_UIA","pid":w["pid"],"hwnd":w["hwnd"],"title":title,"managed":managed}
  if not managed:row["action"]="IGNORE_NOT_ALLOWLISTED"
  else:
   matches=[p for p in cdp if p.get("conversation_id") and p.get("title")==title]
   if not matches:row["action"]="OBSERVE_ONLY_NO_EXACT_CID"
   else:row.update({"conversation_id":matches[0]["conversation_id"],"action":"EXACT_CID_READY"})
  rows.append(row)
 return rows
def main():
 OUT.parent.mkdir(parents=True,exist_ok=True)
 while True:
  try:data={"updated":time.time(),"mode":"ACTIVE_PATROL","interval_seconds":INTERVAL,"new_chat":"FORBIDDEN","windows":cycle()}
  except Exception as e:data={"updated":time.time(),"mode":"ACTIVE_PATROL","error":str(e),"new_chat":"FORBIDDEN"}
  OUT.write_text(json.dumps(data,ensure_ascii=False,indent=2),encoding="utf-8");time.sleep(INTERVAL)
if __name__=="__main__":main()
