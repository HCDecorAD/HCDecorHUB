import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class LiveIdentity(unittest.TestCase):
 def test_read_only(self):
  s=(ROOT/"src"/"adapters"/"live_identity.py").read_text(encoding="utf-8").lower()
  self.assertIn("exact_selected",s);self.assertIn("getvaluepattern",s)
  for x in (".select(","click(","sendkeys","setvalue","createtarget","publicentry"):self.assertNotIn(x,s)
if __name__=="__main__":unittest.main()
