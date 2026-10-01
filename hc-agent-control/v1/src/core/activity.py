STATES={"IDLE","WORKING","WAITING","UNKNOWN"}
def classify_dom_signal(signal):
 if not isinstance(signal,dict):return "UNKNOWN"
 if signal.get("streaming") is True:return "WORKING"
 if signal.get("stop_button") is True:return "WORKING"
 if signal.get("composer_disabled") is True:return "WORKING"
 if signal.get("conversation_ready") is True:return "IDLE"
 return "UNKNOWN"
