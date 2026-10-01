import tempfile,pathlib,json,unittest
from scripts.gate_evidence import validate
class T(unittest.TestCase):
 def test_cp4_weak_denied(self):
  with tempfile.TemporaryDirectory() as d:
   r=pathlib.Path(d);p=r/"e.json";p.write_text(json.dumps({"before":{"alive":True},"after":{"alive":True},"pass":True}),encoding="utf-8")
   self.assertFalse(validate(r,"CP4_RECOVERY",{"evidence":"e.json"}))
 def test_g2_never_generic_promoted(self):self.assertFalse(validate(pathlib.Path("."),"G2_REAL_TARGETED_SEND",{}))
if __name__=="__main__":unittest.main()
