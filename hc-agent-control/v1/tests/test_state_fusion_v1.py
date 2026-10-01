import unittest
from src.core.state_fusion import StateFusion
class StateFusionTest(unittest.TestCase):
 def setUp(self):self.f=StateFusion()
 def test_blocked_precedence(self):self.assertEqual(self.f.classify({"explicit_error":True,"streaming":True}),"BLOCKED")
 def test_working(self):self.assertEqual(self.f.classify({"stop_button":True}),"WORKING")
 def test_lost(self):self.assertEqual(self.f.classify({"identity_exact":True,"identity_age_seconds":121}),"LOST")
 def test_waiting(self):self.assertEqual(self.f.classify({"identity_exact":True,"network_active":True}),"WAITING")
 def test_ready_requires_exact(self):self.assertEqual(self.f.classify({"identity_exact":True,"conversation_visible":True,"composer_enabled":True}),"READY")
 def test_no_exact_fail_closed(self):self.assertEqual(self.f.classify({"conversation_visible":True,"composer_enabled":True}),"UNKNOWN")
if __name__=="__main__":unittest.main()
