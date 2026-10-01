import unittest
from src.core.service import AutoChatService
from src.core.queue import CommandQueue
class D:
 def pages(self):return [{"conversation_id":"abc","target_id":"new","url":"https://chatgpt.com/c/abc"}]
class R:
 def all(self):return [{"alias":"GSC","conversation_id":"abc","target_id":"old"}]
 def get(self,a):return self.all()[0] if a.upper()=="GSC" else None
class Disp:
 def dispatch(self,e,i):return {"dry_run":True,"cid":e["conversation_id"],"id":i["id"]}
class ServiceTests(unittest.TestCase):
 def test_snapshot_restart_safe(self):self.assertEqual(AutoChatService(D(),R(),CommandQueue(),lambda c:Disp()).snapshot()[0]["state"],"ONLINE")
 def test_dry_run(self):self.assertTrue(AutoChatService(D(),R(),CommandQueue(),lambda c:Disp()).dry_run("GSC","/auto")["dry_run"])
if __name__=="__main__":unittest.main()
