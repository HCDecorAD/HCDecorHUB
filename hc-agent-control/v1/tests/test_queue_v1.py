import json,tempfile,unittest
from pathlib import Path
from src.core.queue import CommandQueue
class QueueV1Tests(unittest.TestCase):
 def test_persists_and_deduplicates(self):
  with tempfile.TemporaryDirectory() as d:
   p=Path(d)/"q.json";q=CommandQueue(p);a=q.enqueue("gsc","/auto","same");b=q.enqueue("gsc","/auto","same");self.assertEqual(a["id"],b["id"]);self.assertEqual(len(q.items),1);self.assertEqual(len(CommandQueue(p).items),1)
 def test_stop_all_blocks_next(self):
  q=CommandQueue();q.enqueue("gsc","x");q.stop_all();self.assertIsNone(q.next("gsc"));q.resume();self.assertIsNotNone(q.next("gsc"))
 def test_per_chat_pause_isolated(self):
  q=CommandQueue();q.enqueue("gsc","x");q.enqueue("video","y");q.pause("gsc");self.assertIsNone(q.next("gsc"));self.assertIsNotNone(q.next("video"))
if __name__=="__main__":unittest.main()
