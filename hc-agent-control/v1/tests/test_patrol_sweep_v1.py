import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class PatrolSweep(unittest.TestCase):
 def test_safety(self):
  s=(ROOT/"src"/"adapters"/"patrol_sweep.py").read_text(encoding="utf-8").lower()
  self.assertIn("finally:",s);self.assertIn("orig.getselectionitempattern().select()",s)
  for x in ("sendkeys","setvalue","click(","createtarget","publicentry","livetransaction"):self.assertNotIn(x,s)
if __name__=="__main__":unittest.main()
