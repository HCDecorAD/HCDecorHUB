import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class EdgeWatchdogPolicy(unittest.TestCase):
 def test_watchdog_uses_version_health_and_no_chat_navigation(self):
  s=(ROOT/"src"/"core"/"edge_watchdog.py").read_text(encoding="utf-8").lower()
  self.assertIn("/json/version",s);self.assertNotIn("chatgpt.com",s);self.assertIn("devtoolsactiveport",s)
if __name__=="__main__":unittest.main()
