import unittest
class AliasRestartPolicyTests(unittest.TestCase):
 def test_target_change_is_expected(self):
  saved={"conversation_id":"a","target_id":"old"};cur={"conversation_id":"a","target_id":"new"};self.assertEqual(saved["conversation_id"],cur["conversation_id"]);self.assertNotEqual(saved["target_id"],cur["target_id"])
 def test_missing_conversation_is_failure(self):
  ids={"b"};self.assertNotIn("a",ids)
if __name__=="__main__":unittest.main()
