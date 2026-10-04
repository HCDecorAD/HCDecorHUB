import pathlib,sys
_ROOT=pathlib.Path(__file__).resolve().parents[1]
if str(_ROOT) not in sys.path: sys.path.insert(0,str(_ROOT))
import datetime,tempfile,pathlib
from src.core.queue import CommandQueue
from src.core.live_status import LiveStatusTracker
from src.core.alerts import AlertTracker
with tempfile.TemporaryDirectory() as d:
 q=CommandQueue(pathlib.Path(d)/"q.json");live=LiveStatusTracker();alerts=AlertTracker();now=datetime.datetime.now(datetime.timezone.utc)
 for i in range(5000):
  alias="GSC" if i%2 else "VIDEO";q.enqueue(alias,"/auto",f"k{i}")
  if i%7==0:q.set_state(q.items[-1]["id"],"PASS")
  pages=[{"conversation_id":"c1","target_id":str(i//100)}];live.update(pages,now)
  alerts.transitions({"GSC":live.state("c1",now)})
 if len(q.items)!=5000:raise SystemExit(210)
 q2=CommandQueue(pathlib.Path(d)/"q.json")
 if len(q2.items)!=5000:raise SystemExit(211)
 print("SOAK_CORE_PASS",len(q2.items))
