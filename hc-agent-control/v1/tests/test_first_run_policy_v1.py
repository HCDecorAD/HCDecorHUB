import pathlib,unittest
class T(unittest.TestCase):
 def test_safe_copy(self):
  s=(pathlib.Path(__file__).parents[1]/"src"/"ui"/"first_run.py").read_text(encoding="utf-8")
  self.assertIn("STOP ALL",s);self.assertIn("Real Send remains OFF",s);self.assertIn("/c/",s)
if __name__=="__main__":unittest.main()
