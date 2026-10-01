import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class ExactTransport(unittest.TestCase):
 def test_contract_order(self):
  s=(ROOT/"src"/"adapters"/"uia_exact_transport.py").read_text(encoding="utf-8")
  self.assertLess(s.index("CID_PRECHECK"),s.index("READBACK"));self.assertLess(s.index("READBACK"),s.index("CID_RECHECK"));self.assertLess(s.index("CID_RECHECK"),s.index("{ENTER}"))
  self.assertIn("VERIFIED_SUBMIT",s);self.assertIn("side_effect_uncertain",s);self.assertIn("PREEXISTING_DRAFT",s)
if __name__=="__main__":unittest.main()
