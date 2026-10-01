import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class TabInspector(unittest.TestCase):
 def test_read_only_tab_identity(self):
  s=(ROOT/"src"/"adapters"/"uia_tab_inspector.py").read_text(encoding="utf-8").lower()
  self.assertIn("tabitemcontrol",s);self.assertIn("getselectionitempattern",s)
  for x in ("click(","sendkeys","setvalue","createtarget",".select("):self.assertNotIn(x,s)
if __name__=="__main__":unittest.main()
