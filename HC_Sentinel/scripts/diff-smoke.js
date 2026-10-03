import { mkdir, writeFile } from "node:fs/promises";
import { BasicRgbaDiff } from "../adapters/visual_diff/basic-rgba-diff.js";

const engine = new BasicRgbaDiff();
const baseline = { width: 2, height: 1, data: Uint8Array.from([0,0,0,255,255,255,255,255]) };
const current = { width: 2, height: 1, data: Uint8Array.from([0,0,0,255,250,255,255,255]) };
const identical = engine.compare(baseline, baseline);
const changed = engine.compare(baseline, current);
const tolerant = engine.compare(baseline, current, { channelThreshold: 5 });

const result = { identical, changed, tolerant };
await mkdir(new URL("../evidence/p3/", import.meta.url), { recursive: true });
await writeFile(new URL("../evidence/p3/diff-smoke.json", import.meta.url), JSON.stringify(result, null, 2));
console.log(JSON.stringify(result));
if (!identical.equal || changed.equal || !tolerant.equal) process.exit(2);
