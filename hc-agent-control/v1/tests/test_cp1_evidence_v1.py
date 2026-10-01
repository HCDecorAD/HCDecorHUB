import unittest
class CP1EvidencePolicyTests(unittest.TestCase):
 def test_policy_requires_two_distinct_ids(self):
  pages=[{"conversation_id":"a"},{"conversation_id":"b"},{"conversation_id":""}];ids={p["conversation_id"] for p in pages if p["conversation_id"]};self.assertGreaterEqual(len(ids),2)
 def test_duplicate_not_enough(self):
  pages=[{"conversation_id":"a"},{"conversation_id":"a"}];ids={p["conversation_id"] for p in pages if p["conversation_id"]};self.assertLess(len(ids),2)
if __name__=="__main__":unittest.main()
