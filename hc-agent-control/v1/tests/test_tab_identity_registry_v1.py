import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class RegistryBuilder(unittest.TestCase):
 def test_safety_contract(self):
  s=(ROOT/"scripts"/"BUILD_TAB_IDENTITY_REGISTRY.py").read_text(encoding="utf-8").lower()
  self.assertIn("original_restored",s);self.assertIn("exact_peek_same_title",s);self.assertIn("rejected",s)
  for x in ("sendkeys","setvalue","click(","createtarget","publicentry","livetransaction"):self.assertNotIn(x,s)
if __name__=="__main__":unittest.main()
