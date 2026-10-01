import pathlib,unittest
class T(unittest.TestCase):
 def test_packager_seeds_safe_data(self):
  s=(pathlib.Path(__file__).parents[1]/"scripts"/"51_PACKAGE_STAGING.bat").read_text(encoding="utf-8")
  self.assertIn('"chats":{}',s);self.assertIn('"items":[]',s);self.assertIn('"global_paused":true',s)
  self.assertNotIn("xcopy data ",s.lower())
if __name__=="__main__":unittest.main()
