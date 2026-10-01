import unittest
from src.core.recovery import RecoveryMonitor
class D:
 def pages(self):raise RuntimeError("down")
class T(unittest.TestCase):
 def test_zero_attempts_returns_lost(self):
  r=RecoveryMonitor(D(),ensure=lambda:None,attempts=0).probe();self.assertEqual(r["connection"],"LOST");self.assertIn("down",r["error"])
if __name__=="__main__":unittest.main()
