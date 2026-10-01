class TargetMismatch(RuntimeError):
    pass

class TargetVerifier:
    @staticmethod
    def verify(expected, current):
        expected_id = str(expected.get("conversation_id") or "").strip()
        current_id = str(current.get("conversation_id") or "").strip()
        if not expected_id:
            raise TargetMismatch("expected conversation_id missing")
        if not current_id:
            raise TargetMismatch("current conversation_id missing")
        if expected_id != current_id:
            raise TargetMismatch("conversation_id mismatch")
        return {"ok": True, "conversation_id": current_id, "target_changed": bool(expected.get("target_id") and current.get("target_id") and expected.get("target_id") != current.get("target_id"))}
