import json,tempfile,unittest
from pathlib import Path
from src.core.arming import require_two_key_arm,ArmingDenied
class ArmingTests(unittest.TestCase):
 def test_default_manifest_blocks(self):
  with tempfile.TemporaryDirectory() as d:
   r=Path(d);(r/"manifest.json").write_text(json.dumps({"send_armed":False}),encoding="utf-8")
   with self.assertRaises(ArmingDenied):require_two_key_arm(r)
 def test_requires_token(self):
  with tempfile.TemporaryDirectory() as d:
   r=Path(d);(r/"manifest.json").write_text(json.dumps({"send_armed":True}),encoding="utf-8")
   with self.assertRaises(ArmingDenied):require_two_key_arm(r)
if __name__=="__main__":unittest.main()
