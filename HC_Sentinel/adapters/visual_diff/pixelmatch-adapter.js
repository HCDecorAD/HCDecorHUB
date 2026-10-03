import { VisualDiffEngine } from "./diff-contract.js";

export class PixelmatchAdapter extends VisualDiffEngine {
  async compare(baseline, current, options = {}) {
    let pixelmatch;
    try {
      pixelmatch = (await import("pixelmatch")).default;
    } catch {
      const err = new Error("PIXELMATCH_UNAVAILABLE");
      err.code = "WAITING_CAPABILITY";
      throw err;
    }
    if (baseline.width !== current.width || baseline.height !== current.height) {
      return { equal: false, reason: "DIMENSION_MISMATCH", ratio: 1 };
    }
    const output = new Uint8Array(baseline.data.length);
    const changed = pixelmatch(
      baseline.data, current.data, output,
      baseline.width, baseline.height,
      { threshold: options.threshold ?? 0.1, includeAA: options.includeAA ?? false }
    );
    const total = baseline.width * baseline.height;
    const ratio = total ? changed / total : 0;
    return { equal: ratio <= (options.maxRatio ?? 0), ratio, changedPixels: changed, totalPixels: total, diff: output };
  }
}
