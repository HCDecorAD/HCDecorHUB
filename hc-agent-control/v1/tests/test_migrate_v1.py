import json,tempfile,unittest
from pathlib import Path
from src.core.migrate import migrate
class MigrationTests(unittest.TestCase):
 def test_preserves_aliases(self):
  with tempfile.TemporaryDirectory() as d:
   r=Path(d);(r/"data").mkdir();(r/"data"/"chats.json").write_text(json.dumps({"chats":{"GSC":{"conversation_id":"x"}}}),encoding="utf-8");migrate(r);x=json.loads((r/"data"/"chats.json").read_text());self.assertIn("GSC",x["chats"])
 def test_new_queue_is_paused(self):
  with tempfile.TemporaryDirectory() as d:
   r=Path(d);migrate(r);x=json.loads((r/"data"/"queue.json").read_text());self.assertTrue(x["global_paused"])
if __name__=="__main__":unittest.main()
