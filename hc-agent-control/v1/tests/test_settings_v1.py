import tempfile,unittest
from pathlib import Path
from src.core.settings import Settings
class T(unittest.TestCase):
 def test_persist(self):
  with tempfile.TemporaryDirectory() as d:
   p=Path(d)/"settings.json";s=Settings(p);s.set("theme","dark");self.assertEqual(Settings(p).get("theme"),"dark")
if __name__=="__main__":unittest.main()
