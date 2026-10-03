export const TriggerMode = Object.freeze({
  MANUAL_NOW: "MANUAL_NOW",
  COMMAND: "COMMAND",
  EVENT: "EVENT",
  WATCH: "WATCH",
  SCHEDULED: "SCHEDULED",
  AUTO: "AUTO"
});

export const MissionStatus = Object.freeze({
  IDLE: "IDLE",
  TRIGGERED: "TRIGGERED",
  OBSERVING: "OBSERVING",
  FINDING: "FINDING",
  DIAGNOSED: "DIAGNOSED",
  ROUTED: "ROUTED",
  ACTIONED: "ACTIONED",
  VERIFYING: "VERIFYING",
  GREEN: "GREEN",
  NO_CHANGE: "NO_CHANGE",
  REVIEW_REQUIRED: "REVIEW_REQUIRED",
  BLOCKED: "BLOCKED",
  WAITING_CAPABILITY: "WAITING_CAPABILITY",
  UNCERTAIN_EFFECT: "UNCERTAIN_EFFECT"
});

export function createMission({ id, mode, command = "", priority, target = null }) {
  if (!id) throw new Error("mission id required");
  if (!Object.values(TriggerMode).includes(mode)) throw new Error("invalid trigger mode");
  const effectivePriority = priority ?? (mode === TriggerMode.MANUAL_NOW ? 100 : mode === TriggerMode.COMMAND ? 90 : 50);
  return {
    id, mode, command, target, priority: effectivePriority,
    status: MissionStatus.TRIGGERED,
    checkpoint: null,
    createdAt: new Date().toISOString(),
    effectKeys: []
  };
}
