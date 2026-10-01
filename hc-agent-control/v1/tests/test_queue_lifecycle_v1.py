import unittest
from src.core.queue import CommandQueue
class QueueLifecycleTests(unittest.TestCase):
 def test_retry_cancel_attempts(self):
  q=CommandQueue();x=q.enqueue("GSC","x","k");q.set_state(x["id"],"RUNNING");self.assertEqual(x["attempts"],1);q.retry(x["id"]);self.assertIsNotNone(q.next("GSC"));q.cancel(x["id"]);self.assertIsNone(q.next("GSC"))
 def test_bad_state_rejected(self):
  q=CommandQueue();x=q.enqueue("GSC","x")
  with self.assertRaises(ValueError):q.set_state(x["id"],"BOGUS")
if __name__=="__main__":unittest.main()
