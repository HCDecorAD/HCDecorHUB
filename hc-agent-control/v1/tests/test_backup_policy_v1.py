import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class T(unittest.TestCase):
 def test_no_wmic_locale_timestamp(self):
  for n in ("62_BACKUP_UPGRADE_BASELINE.bat","64_BACKUP_USER_STATE.bat"):
   s=(ROOT/"scripts"/n).read_text(encoding="utf-8").lower();self.assertNotIn("wmic",s);self.assertIn("backup_manager.py",s)
if __name__=="__main__":unittest.main()
