import unittest
from src.adapters.live_identity import CID
class ProjectRouteCID(unittest.TestCase):
 def test_root_route(self):self.assertEqual(CID.search("https://chatgpt.com/c/6abe78d7-26e4-83ec-940b-51e188acf931").group(1),"6abe78d7-26e4-83ec-940b-51e188acf931")
 def test_project_route(self):self.assertEqual(CID.search("https://chatgpt.com/g/g-p-demo-hcdecor-hub/c/6abd29d3-2f60-83ec-9527-db6fee5159ee").group(1),"6abd29d3-2f60-83ec-9527-db6fee5159ee")
if __name__=="__main__":unittest.main()
