import fs from "node:fs";
import path from "node:path";

export const DEFAULT_STATE = {
  schema: "imaster-transport-mesh-state/v1",
  active: null,
  adapters: {},
  mission: null,
  checkpoint: null,
  updatedAt: null
};

export function rankAdapters(config, state = DEFAULT_STATE) {
  return [...(config.adapters || [])]
    .map(a => ({...a, health: state.adapters?.[a.id]?.health || "unknown"}))
    .filter(a => a.health !== "down" && a.health !== "quarantined")
    .sort((a,b) => a.priority - b.priority);
}

export function selectTransport(config, state = DEFAULT_STATE) {
  const ranked = rankAdapters(config, state);
  const healthy = ranked.find(a => a.health === "healthy");
  return healthy || ranked[0] || null;
}

export function failover(config, state, failedId, reason = "transport_failure") {
  const next = structuredClone(state || DEFAULT_STATE);
  next.adapters ||= {};
  next.adapters[failedId] = {
    ...(next.adapters[failedId] || {}),
    health: "quarantined",
    reason,
    failedAt: new Date().toISOString()
  };
  next.active = selectTransport(config, next)?.id || null;
  next.updatedAt = new Date().toISOString();
  return next;
}

export function markHealthy(config, state, id, evidence = null) {
  const next = structuredClone(state || DEFAULT_STATE);
  next.adapters ||= {};
  next.adapters[id] = {health:"healthy", evidence, checkedAt:new Date().toISOString()};
  next.active = selectTransport(config, next)?.id || null;
  next.updatedAt = new Date().toISOString();
  return next;
}

export function loadJson(file, fallback = {}) {
  try { return JSON.parse(fs.readFileSync(file, "utf8")); } catch { return structuredClone(fallback); }
}

export function saveJson(file, value) {
  fs.mkdirSync(path.dirname(file), {recursive:true});
  fs.writeFileSync(file, JSON.stringify(value, null, 2) + "\n", "utf8");
}
