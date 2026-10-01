import unittest
from src.core.resolver import AliasResolver,AliasResolutionError
class ResolverV1Tests(unittest.TestCase):
 def test_restart_target_change_resolves_by_conversation(self):
  saved={"alias":"GSC","conversation_id":"abc","target_id":"old"};pages=[{"conversation_id":"abc","target_id":"new","title":"Renamed"}];self.assertEqual(AliasResolver.resolve(saved,pages)["target_id"],"new")
 def test_title_collision_does_not_bind(self):
  with self.assertRaises(AliasResolutionError):AliasResolver.resolve({"conversation_id":"abc"},[{"conversation_id":"xyz","title":"Same"},{"conversation_id":"def","title":"Same"}])
 def test_duplicate_conversation_is_ambiguous(self):
  with self.assertRaises(AliasResolutionError):AliasResolver.resolve({"conversation_id":"abc"},[{"conversation_id":"abc","target_id":"1"},{"conversation_id":"abc","target_id":"2"}])
if __name__=="__main__":unittest.main()
