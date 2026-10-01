import unittest
from src.core.state_debounce import StateDebouncer
class Debounce(unittest.TestCase):
 def test_ready_two_samples(self):
  d=StateDebouncer(2);self.assertEqual(d.update("x","READY"),"READY_PENDING");self.assertEqual(d.update("x","READY"),"READY_STABLE")
 def test_working_immediate(self):
  self.assertEqual(StateDebouncer(2).update("x","WORKING"),"WORKING")
 def test_reset(self):
  d=StateDebouncer(2);d.update("x","READY");d.update("x","WORKING");self.assertEqual(d.update("x","READY"),"READY_PENDING")
if __name__=="__main__":unittest.main()
