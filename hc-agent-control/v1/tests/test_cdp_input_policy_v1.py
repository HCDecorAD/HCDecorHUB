import pathlib,unittest
R=pathlib.Path(__file__).parents[1]
class T(unittest.TestCase):
 def test_input_uses_cdp_not_coordinates(self):
  s=(R/"src"/"adapters"/"cdp_input.py").read_text(encoding="utf-8")
  self.assertIn("Input.insertText",s);self.assertIn("Input.dispatchKeyEvent",s);self.assertNotIn("mouse",s.lower())
 def test_focus_verified(self):
  s=(R/"src"/"adapters"/"focus_contract.py").read_text(encoding="utf-8")
  self.assertIn("Page.bringToFront",s);self.assertIn("activeElement",s)
if __name__=="__main__":unittest.main()
