import unittest
from src.core.transitions import TransitionTracker
class T(unittest.TestCase):
 def test_transition_and_offline(self):
  x=TransitionTracker();self.assertEqual(x.update({"a":"IDLE"}),[]);e=x.update({"a":"WAITING"});self.assertEqual(e[0]["to"],"WAITING");e=x.update({});self.assertEqual(e[0]["to"],"OFFLINE")
if __name__=="__main__":unittest.main()
