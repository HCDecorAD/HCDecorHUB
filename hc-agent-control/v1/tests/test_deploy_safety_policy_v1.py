import pathlib,unittest
class T(unittest.TestCase):
 def test_deploy_backup_stop_validate(self):
  s=(pathlib.Path(__file__).parents[1]/"scripts"/"69_DEPLOY_EARLY_USE_LOCAL.bat").read_text(encoding="utf-8")
  for x in ("64_BACKUP_USER_STATE.bat","80_RESET_SAFE_STATE.bat","08_MIGRATE_RUNTIME.bat","07_SELFTEST_NO_BROWSER.bat"):self.assertIn(x,s)
if __name__=="__main__":unittest.main()
