import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class FairDispatch(unittest.TestCase):
 def test_pre_side_effect_failure_does_not_return(self):
  s=(ROOT/"src"/"core"/"supervisor_daemon.py").read_text(encoding="utf-8")
  self.assertIn("dispatch_skipped",s);self.assertIn('result.get("side_effect_uncertain")',s)
  self.assertNotIn('row["dispatch"]=result;return {"title"',s)
if __name__=="__main__":unittest.main()
