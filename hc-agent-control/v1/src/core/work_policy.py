class WorkPolicy:
 def decide(self,state):
  return {
   "READY_STABLE":"CONTINUE_EXISTING",
   "READY_PENDING":"WAIT_CONFIRMATION",
   "WORKING":"LEAVE_ALONE",
   "WAITING":"WAIT",
   "BLOCKED":"ROUTE_AUTODEBUG",
   "LOST":"RECOVERY",
   "UNKNOWN":"OBSERVE",
  }.get(state,"OBSERVE")
