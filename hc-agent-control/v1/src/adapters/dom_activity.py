class DOMActivity:
 def classify_snapshot(self,s):
  # Read-only normalized snapshot supplied by an adapter. No clicking or sending.
  if not s:return "UNKNOWN"
  if s.get("streaming") is True:return "WORKING"
  if s.get("stop_button") is True:return "WORKING"
  if s.get("composer_disabled") is True and s.get("assistant_busy") is True:return "WORKING"
  if s.get("conversation_visible") is True:return "IDLE"
  return "UNKNOWN"
