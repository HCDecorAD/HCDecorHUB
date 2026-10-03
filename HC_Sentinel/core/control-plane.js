import { MissionStatus, TriggerMode } from "./mission.js";
import { EventTimeline } from "./events.js";

export class ControlPlane {
  constructor({ timeline = new EventTimeline() } = {}) {
    this.timeline = timeline;
    this.missions = new Map();
    this.effectLedger = new Set();
  }

  submit(mission) {
    if (this.missions.has(mission.id)) {
      this.timeline.emit("DUPLICATE_MISSION", { missionId: mission.id });
      return this.missions.get(mission.id);
    }
    this.missions.set(mission.id, mission);
    this.timeline.emit("MISSION_SUBMIT", { missionId: mission.id, mode: mission.mode, priority: mission.priority });
    return mission;
  }

  next() {
    const active = [...this.missions.values()].filter(m => [MissionStatus.TRIGGERED, MissionStatus.BLOCKED].includes(m.status));
    active.sort((a,b) => b.priority - a.priority || a.createdAt.localeCompare(b.createdAt));
    return active[0] ?? null;
  }

  transition(id, status, meta = {}) {
    const m = this.missions.get(id);
    if (!m) throw new Error("mission not found");
    m.status = status;
    this.timeline.emit("MISSION_STATE", { missionId: id, status, ...meta });
    return m;
  }

  checkpoint(id, data) {
    const m = this.missions.get(id);
    if (!m) throw new Error("mission not found");
    m.checkpoint = { at: new Date().toISOString(), data };
    this.timeline.emit("CHECKPOINT", { missionId: id });
    return m.checkpoint;
  }

  claimEffect(id, effectKey) {
    if (!effectKey) throw new Error("effect key required");
    if (this.effectLedger.has(effectKey)) {
      this.timeline.emit("EFFECT_DUPLICATE_BLOCKED", { missionId: id, effectKey });
      return false;
    }
    this.effectLedger.add(effectKey);
    const m = this.missions.get(id);
    if (m) m.effectKeys.push(effectKey);
    this.timeline.emit("EFFECT_CLAIM", { missionId: id, effectKey });
    return true;
  }

  manualCanPreempt() {
    const next = this.next();
    return next?.mode === TriggerMode.MANUAL_NOW || next?.mode === TriggerMode.COMMAND;
  }
}
