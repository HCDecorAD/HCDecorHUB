import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class T(unittest.TestCase):
 def test_no_manual_pass_prompt(self):
  s=(ROOT/"scripts"/"57_PROMOTE_CP5_AFTER_MANUAL_EXE_SMOKE.bat").read_text(encoding="utf-8").lower()
  self.assertNotIn("set /p",s);self.assertIn("validate_cp5_evidence.py",s)
if __name__=="__main__":unittest.main()
