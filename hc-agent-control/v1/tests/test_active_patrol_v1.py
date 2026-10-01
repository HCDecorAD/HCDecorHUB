import pathlib,unittest
ROOT=pathlib.Path(__file__).parents[1]
class ActivePatrol(unittest.TestCase):
 def test_fail_closed_without_exact_cid(self):
  s=(ROOT/"src"/"core"/"supervisor_daemon.py").read_text(encoding="utf-8")
  self.assertIn("OBSERVE_ONLY_NO_EXACT_CID",s);self.assertIn('new_chat":"FORBIDDEN"',s)
  for x in ("LiveTransaction(","createTarget","PublicEntry"):self.assertNotIn(x,s)
if __name__=="__main__":unittest.main()
