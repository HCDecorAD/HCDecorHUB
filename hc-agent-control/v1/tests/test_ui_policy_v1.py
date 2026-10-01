import unittest
from src.core.registry import ChatRegistry
from src.core.resolver import AliasResolver,AliasResolutionError
class UIPolicyTests(unittest.TestCase):
 def test_root_page_cannot_resolve(self):
  with self.assertRaises(AliasResolutionError):AliasResolver.resolve({"conversation_id":"abc"},[{"conversation_id":"","title":"ChatGPT"}])
 def test_title_rename_irrelevant(self):
  p=AliasResolver.resolve({"conversation_id":"abc","title":"Old"},[{"conversation_id":"abc","title":"New","target_id":"x"}]);self.assertEqual(p["title"],"New")
if __name__=="__main__":unittest.main()
