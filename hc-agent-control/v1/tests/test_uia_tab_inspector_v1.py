import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class TabInspector(unittest.TestCase):
 def test_read_only(self):
  s=(ROOT/"src"/"adapters"/"uia_tab_inspector.py").read_text(encoding="utf-8").lower()
  self.assertIn("tabitemcontrol",s);self.assertIn("addresseditbox",s)
  for x in ("click(","sendkeys","setvalue","createtarget","publicentry"):self.assertNotIn(x,s)
if __name__=="__main__":unittest.main()
