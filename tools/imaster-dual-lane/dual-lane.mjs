#!/usr/bin/env node
const locks = new Set();

export function classify(action = {}) {
  const mutating = ["write","create","rename","send","click","type","save"].includes(String(action.kind||"").toLowerCase());
  return { mutating, preferred: "mcp", fallback: mutating ? "rdc" : null };
}

export async function runDualLane(action, lanes) {
  const target = String(action.target || "global");
  const plan = classify(action);
  const first = await lanes.mcp(action);
  if (first?.ok) return { ok:true, lane:"mcp", result:first };
  if (!plan.fallback) return { ok:false, lane:"mcp", result:first };
  if (locks.has(target)) return { ok:false, error:"TARGET_LOCKED", target };
  locks.add(target);
  try {
    const write = await lanes.rdc(action);
    if (!write?.ok) return { ok:false, lane:"rdc", result:write };
    const verify = lanes.verify ? await lanes.verify(action, write) : { ok:true, evidence:"rdc" };
    return { ok:!!verify?.ok, lane:"rdc", write, verify };
  } finally { locks.delete(target); }
}

if (process.argv[1]?.endsWith("dual-lane.mjs")) {
  console.log(JSON.stringify({ok:true,id:"IMASTER_DUAL_LANE",policy:"MCP-FIRST",writeFallback:"RDC-ON-DEMAND",locking:true,verify:"MCP-FIRST"}));
}
