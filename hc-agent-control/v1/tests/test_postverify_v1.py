import unittest
from src.core.postverify import PostSubmitVerifier,PostSubmitVerificationError
class PostVerifyTests(unittest.TestCase):
 def test_exact_landed_prompt(self):self.assertTrue(PostSubmitVerifier.verify("abc",{"user_message_count":2},{"conversation_id":"abc","user_message_count":3,"last_user_text":"/auto"},"/auto")["ok"])
 def test_changed_conversation_blocks(self):
  with self.assertRaises(PostSubmitVerificationError):PostSubmitVerifier.verify("abc",{"user_message_count":2},{"conversation_id":"xyz","user_message_count":3,"last_user_text":"/auto"},"/auto")
 def test_wrong_text_blocks(self):
  with self.assertRaises(PostSubmitVerificationError):PostSubmitVerifier.verify("abc",{"user_message_count":2},{"conversation_id":"abc","user_message_count":3,"last_user_text":"wrong"},"/auto")
if __name__=="__main__":unittest.main()
