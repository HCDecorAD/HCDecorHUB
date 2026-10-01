import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class T(unittest.TestCase):
 def test_cp4_requires_down(self):
  s=(ROOT/"scripts"/"43_CP4_MANAGED_EDGE_RESTART_TEST.bat").read_text(encoding="utf-8");self.assertIn("--require-down",s)
 def test_restart_requires_prepost_compare(self):
  s=(ROOT/"scripts"/"16_CAPTURE_RESTART_POST.bat").read_text(encoding="utf-8");self.assertIn("compare_restart_evidence.py",s)
 def test_promotion_compileall(self):
  s=(ROOT/"scripts"/"79_PROMOTE_NEXT_TO_EARLY_USE_PRECHECK.bat").read_text(encoding="utf-8");self.assertIn("47_STATIC_COMPILE_ALL.bat",s)
if __name__=="__main__":unittest.main()
