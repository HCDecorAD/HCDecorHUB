import unittest
from src.core.verifier import TargetMismatch,TargetVerifier
class VerifierV1Tests(unittest.TestCase):
    def test_same_conversation_allows_stale_target(self):
        r=TargetVerifier.verify({"conversation_id":"abc","target_id":"old"},{"conversation_id":"abc","target_id":"new"})
        self.assertTrue(r["ok"]);self.assertTrue(r["target_changed"])
    def test_wrong_conversation_blocks(self):
        with self.assertRaises(TargetMismatch): TargetVerifier.verify({"conversation_id":"abc"},{"conversation_id":"xyz"})
    def test_title_alone_never_authorizes(self):
        with self.assertRaises(TargetMismatch): TargetVerifier.verify({"title":"ChatGPT"},{"title":"ChatGPT"})
    def test_missing_current_id_blocks(self):
        with self.assertRaises(TargetMismatch): TargetVerifier.verify({"conversation_id":"abc"},{"title":"ChatGPT"})
if __name__=="__main__": unittest.main()
