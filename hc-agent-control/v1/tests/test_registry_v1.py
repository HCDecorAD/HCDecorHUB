import tempfile,unittest
from pathlib import Path
from src.core.registry import ChatRegistry,RegistryConflict
class RegistryTests(unittest.TestCase):
 def test_persistence_and_collision(self):
  with tempfile.TemporaryDirectory() as d:
   p=Path(d)/"chats.json";r=ChatRegistry(p);r.bind("GSC",{"conversation_id":"a"});self.assertEqual(ChatRegistry(p).get("GSC")["conversation_id"],"a")
   with self.assertRaises(RegistryConflict):r.bind("VIDEO",{"conversation_id":"a"})
   with self.assertRaises(RegistryConflict):r.bind("GSC",{"conversation_id":"b"})
   r.bind("GSC",{"conversation_id":"b"},replace=True);self.assertEqual(r.get("GSC")["conversation_id"],"b")
if __name__=="__main__":unittest.main()
