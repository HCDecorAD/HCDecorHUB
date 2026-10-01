import datetime,unittest
from src.core.heartbeat import classify
class T(unittest.TestCase):
 def test_states(self):
  n=datetime.datetime(2026,1,1,tzinfo=datetime.timezone.utc)
  self.assertEqual(classify(None,n),"OFFLINE")
  self.assertEqual(classify(n.isoformat(),n),"IDLE")
  self.assertEqual(classify((n-datetime.timedelta(seconds=40)).isoformat(),n),"WAITING")
  self.assertEqual(classify((n-datetime.timedelta(seconds=130)).isoformat(),n),"LOST")
  self.assertEqual(classify(n.isoformat(),n,True),"WORKING")
if __name__=="__main__":unittest.main()
