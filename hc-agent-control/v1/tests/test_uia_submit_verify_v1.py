import unittest
from src.adapters.uia_submit_verify import verify_submit
class SubmitVerify(unittest.TestCase):
 def test_contract_source(self):
  import inspect,src.adapters.uia_submit_verify as m;s=inspect.getsource(m)
  self.assertIn("CID_CHANGED",s);self.assertIn("COMPOSER_COMMAND_CLEARED",s);self.assertIn("UNCERTAIN_SIDE_EFFECT",s)
if __name__=="__main__":unittest.main()
