import unittest,tempfile,pathlib,json
from src.core.work_dispatcher import WorkDispatcher
class D(unittest.TestCase):
 def test_only_stable(self):
  d=WorkDispatcher();self.assertFalse(d.eligible({"title":"HC AutoDebug","conversation_id":"x","stable_state":"READY_PENDING","decision":"WAIT_CONFIRMATION"},1000))
 def test_cooldown(self):
  d=WorkDispatcher(600);r={"title":"HC AutoDebug","conversation_id":"x","stable_state":"READY_STABLE","decision":"CONTINUE_EXISTING"};self.assertTrue(d.eligible(r,1000));d.last["x"]=1000;self.assertFalse(d.eligible(r,1100))
 def test_unknown_title(self):
  self.assertFalse(WorkDispatcher().eligible({"title":"X","conversation_id":"x","stable_state":"READY_STABLE","decision":"CONTINUE_EXISTING"},1000))
 def test_persistent_ledger(self):
  with tempfile.TemporaryDirectory() as d:
   p=pathlib.Path(d)/"ledger.json";p.write_text(json.dumps({"last":{"x":1000},"locked":["z"]}),encoding="utf-8");w=WorkDispatcher(600,p)
   r={"title":"HC AutoDebug","conversation_id":"x","stable_state":"READY_STABLE","decision":"CONTINUE_EXISTING"};self.assertFalse(w.eligible(r,1100));self.assertIn("z",w.locked)
if __name__=="__main__":unittest.main()
