import unittest
from src.adapters.dom_activity import DOMActivity
class T(unittest.TestCase):
 def test_read_only_states(self):
  d=DOMActivity();self.assertEqual(d.classify_snapshot({"streaming":True}),"WORKING");self.assertEqual(d.classify_snapshot({"conversation_visible":True}),"IDLE");self.assertEqual(d.classify_snapshot({}),"UNKNOWN")
if __name__=="__main__":unittest.main()
