import tempfile,unittest
from pathlib import Path
from src.core.single_instance import SingleInstance,AlreadyRunning
class SingleInstanceTests(unittest.TestCase):
 def test_lock(self):
  with tempfile.TemporaryDirectory() as d:
   p=Path(d)/"app.lock";a=SingleInstance(p);b=SingleInstance(p);a.acquire()
   with self.assertRaises(AlreadyRunning):b.acquire()
   a.release();self.assertTrue(b.acquire());b.release()
if __name__=="__main__":unittest.main()
