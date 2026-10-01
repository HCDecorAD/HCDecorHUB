import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class SupervisorNoNewChatPolicy(unittest.TestCase):
 def test_supervisor_has_no_new_chat_primitive(self):
  s=(ROOT/"src"/"core"/"supervisor_daemon.py").read_text(encoding="utf-8").lower()
  for token in ("createtarget","publicentry","target.createtarget","chatgpt.com/"):
   self.assertNotIn(token,s)
 def test_edge_recovery_never_navigates_chatgpt(self):
  s=(ROOT/"scripts"/"02_START_MANAGED_EDGE.bat").read_text(encoding="utf-8").lower()
  self.assertNotIn("chatgpt.com/",s);self.assertIn("about:blank",s)
if __name__=="__main__":unittest.main()
