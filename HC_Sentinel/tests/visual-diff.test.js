import test from "node:test";
import assert from "node:assert/strict";
import { BasicRgbaDiff } from "../adapters/visual_diff/basic-rgba-diff.js";

const img = (pixels) => ({ width: pixels.length, height: 1, data: Uint8Array.from(pixels.flat()) });

test("identical images pass", () => {
  const d = new BasicRgbaDiff();
  const a = img([[0,0,0,255],[255,255,255,255]]);
  const r = d.compare(a,a);
  assert.equal(r.equal,true);
  assert.equal(r.changedPixels,0);
});

test("changed pixel is detected", () => {
  const d = new BasicRgbaDiff();
  const a = img([[0,0,0,255],[255,255,255,255]]);
  const b = img([[0,0,0,255],[250,255,255,255]]);
  const r = d.compare(a,b);
  assert.equal(r.equal,false);
  assert.equal(r.changedPixels,1);
  assert.equal(r.ratio,0.5);
});

test("threshold suppresses insignificant channel drift", () => {
  const d = new BasicRgbaDiff();
  const a = img([[100,100,100,255]]);
  const b = img([[103,100,100,255]]);
  assert.equal(d.compare(a,b,{channelThreshold:3}).equal,true);
});
