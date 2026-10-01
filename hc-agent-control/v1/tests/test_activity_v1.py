import unittest
from src.core.activity import classify_dom_signal
class T(unittest.TestCase):
 def test_signals(self):
  self.assertEqual(classify_dom_signal({"streaming":True}),"WORKING")
  self.assertEqual(classify_dom_signal({"stop_button":True}),"WORKING")
  self.assertEqual(classify_dom_signal({"conversation_ready":True}),"IDLE")
  self.assertEqual(classify_dom_signal({}),"UNKNOWN")
if __name__=="__main__":unittest.main()
