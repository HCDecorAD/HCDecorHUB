import pathlib,unittest
class T(unittest.TestCase):
 def test_python_runner(self):
  root=pathlib.Path(__file__).parents[1];s=(root/"scripts"/"RUN_PARALLEL_CHECKS.bat").read_text(encoding="utf-8");self.assertIn("parallel_checks.py",s);self.assertNotIn("start ",s.lower())
if __name__=="__main__":unittest.main()
