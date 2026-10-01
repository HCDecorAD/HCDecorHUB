class StateFusion:
 """Pure fail-closed classifier. Sensors provide normalized read-only facts."""
 def classify(self,s):
  if not s:return "UNKNOWN"
  if s.get("explicit_error") or s.get("retry_button") or s.get("blocked_banner"):return "BLOCKED"
  if s.get("streaming") or s.get("stop_button") or (s.get("composer_disabled") and s.get("assistant_busy")):return "WORKING"
  age=s.get("identity_age_seconds")
  if age is not None and age>120:return "LOST"
  if s.get("identity_exact") is not True:return "UNKNOWN"
  if s.get("network_active") or s.get("dom_mutating") or s.get("recent_focus_event"):return "WAITING"
  if s.get("conversation_visible") and s.get("composer_enabled") is True:return "READY"
  return "UNKNOWN"
