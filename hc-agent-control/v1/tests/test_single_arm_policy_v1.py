import pathlib,unittest
class T(unittest.TestCase):
 def test_arm_is_single_use_contract(self):
  s=(pathlib.Path(__file__).parents[1]/"scripts"/"autochat_arm_once.py").read_text(encoding="utf-8");self.assertIn('"uses":1',s)
if __name__=="__main__":unittest.main()
