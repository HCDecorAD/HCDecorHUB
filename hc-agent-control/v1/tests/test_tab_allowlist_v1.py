import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class TabAllowlist(unittest.TestCase):
 def test_tab_scope_and_safety(self):
  s=(ROOT/"src"/"core"/"supervisor_daemon.py").read_text(encoding="utf-8").lower()
  self.assertIn('"scope":"tab_allowlist"',s);self.assertIn("managed_not_selected",s);self.assertIn('"send":"locked"',s)
  for x in ("createtarget","publicentry","livetransaction"):self.assertNotIn(x,s)
if __name__=="__main__":unittest.main()
