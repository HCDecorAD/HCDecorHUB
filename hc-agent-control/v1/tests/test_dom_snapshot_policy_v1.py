import unittest
from src.adapters.chatgpt_dom import SNAPSHOT_JS
class T(unittest.TestCase):
 def test_snapshot_identity_and_user_messages(self):
  self.assertIn("location.pathname",SNAPSHOT_JS);self.assertIn('data-message-author-role="user"',SNAPSHOT_JS)
if __name__=="__main__":unittest.main()
