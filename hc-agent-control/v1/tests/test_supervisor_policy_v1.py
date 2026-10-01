import unittest
from src.core.supervisor import SupervisorPolicy
class SupervisorPolicyTests(unittest.TestCase):
 def setUp(self):self.p=SupervisorPolicy(cooldown=60,max_continues=2);self.page={"conversation_id":"cid-1","title":"HC AutoDebug"}
 def test_existing_chat_is_default(self):
  r=self.p.choose(self.page,"IDLE",now=100);self.assertEqual(r["action"],"CONTINUE_EXISTING");self.assertEqual(r["conversation_id"],"cid-1")
 def test_working_never_interrupted(self):self.assertEqual(self.p.choose(self.page,"WORKING",now=100)["action"],"SKIP")
 def test_new_chat_only_when_limit_confirmed(self):self.assertEqual(self.p.choose(self.page,"IDLE",now=100,limited=True)["action"],"HANDOFF_NEW_CHAT")
 def test_cooldown_and_continue_limit(self):
  self.p.mark_sent("cid-1",100);self.assertEqual(self.p.choose(self.page,"IDLE",now=120)["reason"],"COOLDOWN")
  self.assertEqual(self.p.choose(self.page,"IDLE",now=161)["action"],"CONTINUE_EXISTING");self.p.mark_sent("cid-1",161)
  self.assertEqual(self.p.choose(self.page,"IDLE",now=222)["action"],"REVIEW_REQUIRED")
if __name__=="__main__":unittest.main()
