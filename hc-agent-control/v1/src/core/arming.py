import json,pathlib
class ArmingDenied(RuntimeError):pass
def require_two_key_arm(root):
 root=pathlib.Path(root);m=json.loads((root/"manifest.json").read_text(encoding="utf-8"))
 if not bool(m.get("send_armed")):raise ArmingDenied("manifest send_armed=false")
 token=root/"runtime"/"G2_LIVE_ARM.json"
 if not token.exists():raise ArmingDenied("live arm token missing")
 d=json.loads(token.read_text(encoding="utf-8"))
 if d.get("scope")!="G2_SINGLE_COMMAND" or not d.get("conversation_id"):raise ArmingDenied("invalid live arm token")
 return d
