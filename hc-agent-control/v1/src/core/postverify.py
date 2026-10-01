class PostSubmitVerificationError(RuntimeError):pass
class PostSubmitVerifier:
 @staticmethod
 def verify(expected_conversation_id,before,after,command_text):
  if str(after.get("conversation_id") or "")!=str(expected_conversation_id):raise PostSubmitVerificationError("conversation changed after submit")
  before_count=int(before.get("user_message_count",0));after_count=int(after.get("user_message_count",0))
  if after_count<=before_count:raise PostSubmitVerificationError("no new user message observed")
  landed=str(after.get("last_user_text") or "").strip()
  if landed!=str(command_text).strip():raise PostSubmitVerificationError("landed text mismatch")
  return {"ok":True,"conversation_id":expected_conversation_id,"last_user_text":landed}
