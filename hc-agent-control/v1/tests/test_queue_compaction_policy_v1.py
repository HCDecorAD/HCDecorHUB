import pathlib,unittest
class T(unittest.TestCase):
 def test_only_terminal_archived(self):
  s=(pathlib.Path(__file__).parents[1]/"src"/"core"/"queue_maintenance.py").read_text(encoding="utf-8")
  self.assertIn('TERMINAL={"PASS","FAILED","CANCELLED"}',s)
  for x in ("READY","RUNNING","RETRY"):self.assertNotIn('"'+x+'"',s.split("TERMINAL=")[1].split("\n")[0])
if __name__=="__main__":unittest.main()
