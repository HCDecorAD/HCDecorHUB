class AliasResolutionError(RuntimeError):pass
class AliasResolver:
 @staticmethod
 def resolve(saved,current_pages):
  cid=str(saved.get("conversation_id") or "").strip()
  if not cid:raise AliasResolutionError("saved conversation_id missing")
  matches=[p for p in current_pages if str(p.get("conversation_id") or "").strip()==cid]
  if len(matches)==0:raise AliasResolutionError("conversation offline")
  if len(matches)>1:raise AliasResolutionError("ambiguous conversation_id")
  return matches[0]
