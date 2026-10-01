import tempfile,unittest,pathlib
from src.core.queue import CommandQueue
from src.core.queue_maintenance import compact
class T(unittest.TestCase):
 def test_compact_keeps_active(self):
  with tempfile.TemporaryDirectory() as d:
   q=CommandQueue(pathlib.Path(d)/"q.json")
   for i in range(10):
    x=q.enqueue("GSC","x",str(i))
    if i<8:q.set_state(x["id"],"PASS")
   r=compact(q,pathlib.Path(d)/"archive.json",3);self.assertEqual(r["archived"],5);self.assertEqual(len(q.items),5)
if __name__=="__main__":unittest.main()
