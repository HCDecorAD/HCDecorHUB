import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class DesktopScannerPolicy(unittest.TestCase):
 def test_scanner_is_read_only_and_has_no_new_chat(self):
  s=(ROOT/"src"/"adapters"/"desktop_scanner.py").read_text(encoding="utf-8").lower()
  self.assertIn("enumwindows",s);self.assertIn("getwindowtext",s)
  for x in ("createtarget","chatgpt.com","publicentry","sendinput"):self.assertNotIn(x,s)
if __name__=="__main__":unittest.main()
