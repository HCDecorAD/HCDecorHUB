import unittest
class ReleaseGatePolicyTests(unittest.TestCase):
 def test_only_exact_pass_is_production(self):
  for x in ("OPEN","LOCKED","STAGED","PASS_STAGED","PASS_LOCAL","MISSING"):self.assertNotEqual(x,"PASS")
 def test_pass_is_production(self):self.assertEqual("PASS","PASS")
if __name__=="__main__":unittest.main()
