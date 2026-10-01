import unittest
from src.adapters.cdp_exact import ExactCDPAdapter
class D:
 def __init__(self,p):self.p=p
 def pages(self):return self.p
class T(unittest.TestCase):
 def test_exact_only(self):
  a=ExactCDPAdapter(D([{"conversation_id":"a","target_id":"1"},{"conversation_id":"b","target_id":"2"}]))
  self.assertEqual(a.inspect({"conversation_id":"b"})["target_id"],"2")
 def test_ambiguous_blocked(self):
  a=ExactCDPAdapter(D([{"conversation_id":"a"},{"conversation_id":"a"}]))
  with self.assertRaises(RuntimeError):a.inspect({"conversation_id":"a"})
 def test_send_locked(self):
  with self.assertRaises(RuntimeError):ExactCDPAdapter(D([])).send({},"x","k")
if __name__=="__main__":unittest.main()
