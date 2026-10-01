import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class UIARoundtrip(unittest.TestCase):
 def test_no_submit(self):
  s=(ROOT/"src"/"adapters"/"uia_composer_probe.py").read_text(encoding="utf-8").lower()
  self.assertIn("{ctrl}c",s);self.assertNotIn("{enter}",s);self.assertNotIn("click(",s)
if __name__=="__main__":unittest.main()
