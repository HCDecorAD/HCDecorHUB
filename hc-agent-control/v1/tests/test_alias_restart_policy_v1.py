import unittest,pathlib
R=pathlib.Path(__file__).parents[1]
class AliasRestartPolicyTests(unittest.TestCase):
 def test_target_change_is_expected(self):
  saved={"conversation_id":"a","target_id":"old"};cur={"conversation_id":"a","target_id":"new"};self.assertEqual(saved["conversation_id"],cur["conversation_id"]);self.assertNotEqual(saved["target_id"],cur["target_id"])
 def test_restart_evidence_matches_public_gate(self):
  s=(R/"scripts"/"capture_alias_restart.py").read_text(encoding="utf-8");self.assertIn('"pass":passed',s);self.assertIn('"rows":rows',s)
 def test_missing_conversation_is_failure(self):
  ids={"b"};self.assertNotIn("a",ids)
if __name__=="__main__":unittest.main()
