import json,urllib.request
from .cdp import CDPDiscovery
class ExactCDPAdapter:
 def __init__(self,discovery=None):self.discovery=discovery or CDPDiscovery()
 def inspect(self,expected):
  cid=str(expected.get("conversation_id") or "")
  if not cid:raise RuntimeError("conversation_id required")
  hits=[p for p in self.discovery.pages() if p.get("conversation_id")==cid]
  if len(hits)!=1:raise RuntimeError("exact conversation target unavailable or ambiguous")
  return hits[0]
 def send(self,current,text,idempotency_key):
  raise RuntimeError("CDP exact send transport not armed")
