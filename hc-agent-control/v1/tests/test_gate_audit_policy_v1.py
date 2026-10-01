import pathlib,unittest
class T(unittest.TestCase):
 def test_literal_pass_rule_present(self):
  s=(pathlib.Path(__file__).parents[1]/"scripts"/"gate_audit.py").read_text(encoding="utf-8");self.assertIn('=="PASS"',s.replace(" ",""))
if __name__=="__main__":unittest.main()
