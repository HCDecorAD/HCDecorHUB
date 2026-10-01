import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class UIAActivity(unittest.TestCase):
 def test_read_only(self):
  s=(ROOT/"src"/"adapters"/"uia_activity.py").read_text(encoding="utf-8").lower()
  for x in (".select(","click(","sendkeys","setvalue","createtarget"):self.assertNotIn(x,s)
  self.assertIn("stop_button",s);self.assertIn("composer_enabled",s)
if __name__=="__main__":unittest.main()
