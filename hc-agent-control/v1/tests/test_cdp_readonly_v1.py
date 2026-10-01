import unittest
from src.adapters.cdp_ws import CDPReadOnlyContract,activity_expression
class T(unittest.TestCase):
 def test_allow(self):self.assertTrue(CDPReadOnlyContract.validate("Runtime.evaluate"))
 def test_block(self):
  for x in ("Input.insertText","Page.navigate","DOM.setAttributeValue","Runtime.callFunctionOn"):
   with self.assertRaises(PermissionError):CDPReadOnlyContract.validate(x)
 def test_expr(self):self.assertIn("stop-button",activity_expression())
if __name__=="__main__":unittest.main()
