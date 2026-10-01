VALID_CONNECTION={"ONLINE","OFFLINE","LOST","RECOVERING"}
VALID_AGENT={"IDLE","FOCUSING","TYPING","RESPONDING","WAITING","ERROR"}
class StatusStore:
 def __init__(self):self.items={}
 def set(self,alias,connection,agent):
  if connection not in VALID_CONNECTION:raise ValueError(connection)
  if agent not in VALID_AGENT:raise ValueError(agent)
  self.items[alias.upper()]={"connection":connection,"agent":agent};return self.items[alias.upper()]
