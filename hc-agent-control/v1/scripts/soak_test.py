import tempfile,pathlib,datetime,json
from src.core.queue import CommandQueue
from src.core.live_status import LiveStatusTracker
from src.core.transitions import TransitionTracker
with tempfile.TemporaryDirectory() as d:
 q=CommandQueue(pathlib.Path(d)/"queue.json");live=LiveStatusTracker();tr=TransitionTracker();now=datetime.datetime.now(datetime.timezone.utc)
 for i in range(5000):
  a=("GSC","VIDEO","VISUAL")[i%3];q.enqueue(a,"/auto",f"k{i}")
  if i%7==0:q.set_state(q.items[-1]["id"],"PASS")
  pages=[{"conversation_id":a,"target_id":str(i)}];live.update(pages,now);tr.update({a:live.state(a,now)})
 if len(q.items)!=5000:raise SystemExit("SOAK_QUEUE_COUNT_FAIL")
 raw=json.loads((pathlib.Path(d)/"queue.json").read_text(encoding="utf-8"))
 if len(raw["items"])!=5000:raise SystemExit("SOAK_PERSIST_FAIL")
 print("SOAK_TEST_PASS",len(q.items))
