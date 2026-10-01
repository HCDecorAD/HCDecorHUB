import pathlib,tempfile
from src.core.queue import CommandQueue
with tempfile.TemporaryDirectory() as d:
 p=pathlib.Path(d)/"q.json";q=CommandQueue(p)
 for i in range(1000):q.enqueue("GSC" if i%2==0 else "VIDEO",f"cmd-{i}",f"k-{i}")
 assert len(q.items)==1000
 for i in range(1000):q.enqueue("GSC",f"dup-{i}",f"k-{i}")
 assert len(q.items)==1000
 q.stop_all();assert q.next("GSC") is None and q.next("VIDEO") is None
 q.resume();assert q.next("GSC") is not None and q.next("VIDEO") is not None
 q2=CommandQueue(p);assert len(q2.items)==1000
print("QUEUE_STRESS_1000_PASS")
