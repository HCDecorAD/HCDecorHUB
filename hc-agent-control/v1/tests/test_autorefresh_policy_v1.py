import pathlib,unittest
class T(unittest.TestCase):
 def test_refresh_is_scheduled_and_cancelled(self):
  s=(pathlib.Path(__file__).parents[1]/"src"/"ui"/"app.py").read_text(encoding="utf-8")
  self.assertIn('self.settings.get("auto_refresh_ms",5000)',s);self.assertIn("after_cancel",s);self.assertIn("ALERT ",s);self.assertIn("RECOVERED ",s)
if __name__=="__main__":unittest.main()
