import unittest
from src.adapters.chromium_profiles import chromium_profiles
class ChromiumProfiles(unittest.TestCase):
 def test_shape(self):
  for x in chromium_profiles():
   self.assertIn(x["browser"],("edge","chrome"));self.assertTrue(x["profile"]);self.assertTrue(x["sessions"])
if __name__=="__main__":unittest.main()
