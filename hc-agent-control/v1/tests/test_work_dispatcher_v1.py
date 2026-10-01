import unittest,tempfile,pathlib,json
from src.core.work_dispatcher import WorkDispatcher
def row(state="READY_STABLE",cid="x"):return {"title":"HC AutoDebug","conversation_id":cid,"stable_state":state,"decision":"CONTINUE_EXISTING"}
class D(unittest.TestCase):
 def test_only_stable(self):self.assertFalse(WorkDispatcher().eligible({"title":"HC AutoDebug","conversation_id":"x","stable_state":"READY_PENDING","decision":"WAIT_CONFIRMATION"},1000))
 def test_cooldown(self):
  d=WorkDispatcher(600);self.assertTrue(d.eligible(row(),1000));d.last["x"]=1000;self.assertFalse(d.eligible(row(),1100))
 def test_unknown_title(self):self.assertFalse(WorkDispatcher().eligible({"title":"X","conversation_id":"x","stable_state":"READY_STABLE","decision":"CONTINUE_EXISTING"},1000))
 def test_persistent_ledger(self):
  with tempfile.TemporaryDirectory() as d:
   p=pathlib.Path(d)/"ledger.json";p.write_text(json.dumps({"last":{"x":1000},"locked":["z"],"awaiting_ack":["x"],"seen_busy":[]}),encoding="utf-8");w=WorkDispatcher(600,p)
   self.assertFalse(w.eligible(row(),9999));self.assertIn("z",w.locked);self.assertIn("x",w.awaiting_ack)
 def test_ack_requires_busy_then_ready(self):
  d=WorkDispatcher(0);d.awaiting_ack.add("x");self.assertFalse(d.eligible(row(),1000))
  d.observe(row("WORKING"));self.assertIn("x",d.awaiting_ack);self.assertIn("x",d.seen_busy)
  d.observe(row("READY_STABLE"));self.assertNotIn("x",d.awaiting_ack);self.assertTrue(d.eligible(row(),1001))
if __name__=="__main__":unittest.main()
