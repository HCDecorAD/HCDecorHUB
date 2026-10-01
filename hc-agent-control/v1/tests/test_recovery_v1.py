import unittest
from src.core.recovery import RecoveryMonitor
class D:
 def __init__(self,fail=0):self.fail=fail
 def pages(self):
  if self.fail:self.fail-=1;raise OSError("cdp down")
  return [{"conversation_id":"abc"}]
class RecoveryTests(unittest.TestCase):
 def test_online(self):self.assertEqual(RecoveryMonitor(D()).probe()["connection"],"ONLINE")
 def test_offline_without_ensure(self):self.assertEqual(RecoveryMonitor(D(1)).probe()["connection"],"OFFLINE")
 def test_recovers_after_ensure(self):
  d=D(1);called=[];r=RecoveryMonitor(d,ensure=lambda:called.append(1),attempts=1,delay=0).probe();self.assertTrue(r["recovered"]);self.assertEqual(len(called),1)
if __name__=="__main__":unittest.main()
