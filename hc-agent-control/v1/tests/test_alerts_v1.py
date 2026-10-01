import unittest
from src.core.alerts import AlertTracker
class T(unittest.TestCase):
 def test_only_bad_transition_alerts(self):
  a=AlertTracker();self.assertEqual(a.transitions({"GSC":"IDLE"}),[]);x=a.transitions({"GSC":"LOST"});self.assertEqual(x[0]["to"],"LOST");self.assertEqual(a.transitions({"GSC":"LOST"}),[])
if __name__=="__main__":unittest.main()
