import pathlib,unittest
class ManagedEdgeRecoveryPolicy(unittest.TestCase):
 def test_recovery_does_not_open_chatgpt_new_chat(self):
  s=(pathlib.Path(__file__).parents[1]/"scripts"/"02_START_MANAGED_EDGE.bat").read_text(encoding="utf-8").lower()
  self.assertNotIn("https://chatgpt.com/",s);self.assertIn("about:blank",s)
 def test_recovery_verifies_cdp_before_success(self):
  s=(pathlib.Path(__file__).parents[1]/"scripts"/"02_START_MANAGED_EDGE.bat").read_text(encoding="utf-8").lower()
  self.assertIn("/json/version",s);self.assertIn("managed_edge_ready",s)
if __name__=="__main__":unittest.main()
