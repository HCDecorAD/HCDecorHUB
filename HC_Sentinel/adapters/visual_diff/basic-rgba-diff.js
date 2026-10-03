import { VisualDiffEngine } from "./diff-contract.js";

function validate(img) {
  if (!img || !Number.isInteger(img.width) || !Number.isInteger(img.height) || !img.data) throw new Error("invalid image");
  if (img.data.length !== img.width * img.height * 4) throw new Error("invalid RGBA length");
}

export class BasicRgbaDiff extends VisualDiffEngine {
  compare(baseline, current, options = {}) {
    validate(baseline); validate(current);
    if (baseline.width !== current.width || baseline.height !== current.height) {
      return { equal: false, reason: "DIMENSION_MISMATCH", ratio: 1, changedPixels: Math.max(baseline.width*baseline.height,current.width*current.height) };
    }
    const channelThreshold = options.channelThreshold ?? 0;
    const total = baseline.width * baseline.height;
    let changed = 0;
    for (let p = 0; p < total; p++) {
      const i = p * 4;
      let different = false;
      for (let c = 0; c < 4; c++) {
        if (Math.abs(baseline.data[i+c] - current.data[i+c]) > channelThreshold) { different = true; break; }
      }
      if (different) changed++;
    }
    const ratio = total === 0 ? 0 : changed / total;
    const maxRatio = options.maxRatio ?? 0;
    return { equal: ratio <= maxRatio, ratio, changedPixels: changed, totalPixels: total };
  }
}
