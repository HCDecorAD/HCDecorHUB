import test from "node:test";
import assert from "node:assert/strict";
import { ControlPlane } from "../core/control-plane.js";
import { createMission, TriggerMode, MissionStatus } from "../core/mission.js";

test("manual mission preempts scheduled mission", () => {
  const cp = new ControlPlane();
  cp.submit(createMission({ id: "s1", mode: TriggerMode.SCHEDULED }));
  cp.submit(createMission({ id: "m1", mode: TriggerMode.MANUAL_NOW }));
  assert.equal(cp.next().id, "m1");
  assert.equal(cp.manualCanPreempt(), true);
});

test("duplicate mission is idempotent", () => {
  const cp = new ControlPlane();
  const m = createMission({ id: "x", mode: TriggerMode.COMMAND });
  assert.equal(cp.submit(m), m);
  assert.equal(cp.submit({ ...m, command: "changed" }), m);
});

test("effect ledger blocks duplicate side effect", () => {
  const cp = new ControlPlane();
  cp.submit(createMission({ id: "x", mode: TriggerMode.COMMAND }));
  assert.equal(cp.claimEffect("x", "deploy:abc"), true);
  assert.equal(cp.claimEffect("x", "deploy:abc"), false);
});

test("checkpoint and transition persist mission state in control plane", () => {
  const cp = new ControlPlane();
  cp.submit(createMission({ id: "x", mode: TriggerMode.COMMAND }));
  cp.transition("x", MissionStatus.OBSERVING);
  const ck = cp.checkpoint("x", { step: 1 });
  assert.equal(cp.missions.get("x").status, MissionStatus.OBSERVING);
  assert.equal(ck.data.step, 1);
});
