import unittest
from src.core.dispatcher import Dispatcher,DispatchBlocked
from src.core.queue import CommandQueue
class Adapter:
 def __init__(self,current):self.current=current;self.sent=0
 def inspect(self,expected):return self.current
 def send(self,target,text,key):self.sent+=1;return {"ok":True}
class DispatcherV1Tests(unittest.TestCase):
 def test_dry_run_never_sends(self):
  a=Adapter({"conversation_id":"abc","target_id":"new"});d=Dispatcher(a,armed=False);r=d.dispatch({"conversation_id":"abc","target_id":"old"},{"id":"1","idempotency_key":"k","alias":"GSC","text":"x"});self.assertTrue(r["dry_run"]);self.assertEqual(a.sent,0)
 def test_stop_all_blocks(self):
  q=CommandQueue();q.stop_all();a=Adapter({"conversation_id":"abc"});d=Dispatcher(a,queue=q,armed=True)
  with self.assertRaises(DispatchBlocked):d.dispatch({"conversation_id":"abc"},{"id":"1","idempotency_key":"k","alias":"GSC","text":"x"})
  self.assertEqual(a.sent,0)
if __name__=="__main__":unittest.main()
