import datetime
def classify(last_seen,now=None,working=False):
 now=now or datetime.datetime.now(datetime.timezone.utc)
 if not last_seen:return "OFFLINE"
 try:t=datetime.datetime.fromisoformat(str(last_seen).replace("Z","+00:00"))
 except Exception:return "UNKNOWN"
 age=(now-t).total_seconds()
 if age>120:return "LOST"
 if working:return "WORKING"
 if age>30:return "WAITING"
 return "IDLE"
