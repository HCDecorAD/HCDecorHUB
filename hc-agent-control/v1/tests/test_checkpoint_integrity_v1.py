import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class T(unittest.TestCase):
 def test_hash_written_and_checked(self):
  w=(ROOT/"scripts"/"write_checkpoint.py").read_text(encoding="utf-8");p=(ROOT/"scripts"/"promote_gate.py").read_text(encoding="utf-8")
  self.assertIn("evidence_sha256",w);self.assertIn("evidence hash mismatch",p)
if __name__=="__main__":unittest.main()
