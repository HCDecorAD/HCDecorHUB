import pathlib,unittest
from src.adapters.uia_tab_inspector import clean_title
ROOT=pathlib.Path(__file__).parents[1]
class TabIdentity(unittest.TestCase):
 def test_clean(self):self.assertEqual(clean_title("HC AutoDebug - Mức sử dụng bộ nhớ - 590 MB"),"HC AutoDebug")
 def test_read_only(self):
  s=(ROOT/"src"/"adapters"/"uia_tab_inspector.py").read_text(encoding="utf-8").lower()
  for x in (".select(","click(","sendkeys","setvalue","createtarget"):self.assertNotIn(x,s)
if __name__=="__main__":unittest.main()
