import datetime,unittest
from src.core.live_status import LiveStatusTracker
class T(unittest.TestCase):
 def test_lifecycle(self):
  n=datetime.datetime(2026,1,1,tzinfo=datetime.timezone.utc);s=LiveStatusTracker();self.assertEqual(s.state("x",n),"OFFLINE");s.update([{"conversation_id":"x","target_id":"a"}],n);self.assertEqual(s.state("x",n),"IDLE");self.assertEqual(s.state("x",n+datetime.timedelta(seconds=40)),"WAITING");self.assertEqual(s.state("x",n+datetime.timedelta(seconds=121)),"LOST")
 def test_target_change(self):
  n=datetime.datetime.now(datetime.timezone.utc);s=LiveStatusTracker();s.update([{"conversation_id":"x","target_id":"a"}],n);r=s.update([{"conversation_id":"x","target_id":"b"}],n);self.assertTrue(r["x"]["target_changed"])
if __name__=="__main__":unittest.main()
