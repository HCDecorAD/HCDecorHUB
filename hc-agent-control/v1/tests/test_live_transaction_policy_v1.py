import pathlib,unittest
R=pathlib.Path(__file__).parents[1]
class T(unittest.TestCase):
 def test_pre_submit_order(self):
  s=(R/"src"/"core"/"live_transaction.py").read_text(encoding="utf-8")
  keys=["snapshot(page)","FocusContract.focus","insert_text","composer_text","identity changed before submit","press_enter","PostSubmitVerifier.verify"]
  pos=[s.index(k) for k in keys];self.assertEqual(pos,sorted(pos))
 def test_no_enter_before_readback(self):
  s=(R/"src"/"core"/"live_transaction.py").read_text(encoding="utf-8")
  self.assertLess(s.index("composer_text"),s.index("press_enter"))
 def test_runner_consumes_token_before_transaction(self):
  s=(R/"scripts"/"a4_live_once.py").read_text(encoding="utf-8")
  self.assertIn("command_sha256",s);self.assertIn("expires",s)
  self.assertLess(s.index("tok.unlink()"),s.index("LiveTransaction"))
if __name__=="__main__":unittest.main()
