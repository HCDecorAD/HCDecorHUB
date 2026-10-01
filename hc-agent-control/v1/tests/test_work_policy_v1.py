import unittest
from src.core.work_policy import WorkPolicy
class WorkPolicyTest(unittest.TestCase):
 def test_matrix(self):
  p=WorkPolicy()
  expected={"READY_STABLE":"CONTINUE_EXISTING","READY_PENDING":"WAIT_CONFIRMATION","WORKING":"LEAVE_ALONE","BLOCKED":"ROUTE_AUTODEBUG","LOST":"RECOVERY","UNKNOWN":"OBSERVE"}
  for s,a in expected.items():self.assertEqual(p.decide(s),a)
 def test_never_new_chat(self):
  p=WorkPolicy()
  for s in ("READY_STABLE","WORKING","WAITING","BLOCKED","LOST","UNKNOWN"):self.assertNotIn("NEW_CHAT",p.decide(s))
if __name__=="__main__":unittest.main()
