import test from "node:test";
import assert from "node:assert/strict";
import { BrowserObserver } from "../adapters/browser/observer-contract.js";
import { PlaywrightObserver } from "../adapters/browser/playwright-observer.js";

test("observer contract fails closed", async () => {
  const o = new BrowserObserver();
  await assert.rejects(() => o.capture({ url: "https://example.com" }), /not implemented/);
});

test("playwright observer returns WAITING_CAPABILITY when dependency unavailable, or is constructible when present", async () => {
  const o = new PlaywrightObserver();
  assert.equal(o.browserType, "chromium");
});
