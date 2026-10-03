import { mkdir, writeFile } from "node:fs/promises";
import { PlaywrightObserver } from "../adapters/browser/playwright-observer.js";

const outDir = new URL("../evidence/p2/", import.meta.url);
await mkdir(outDir, { recursive: true });

const observer = new PlaywrightObserver();
const result = await observer.capture({ url: "https://example.com", waitUntil: "domcontentloaded", timeoutMs: 30000 }, {
  viewport: { width: 1280, height: 720 },
  fullPage: true
});

await writeFile(new URL("example.png", outDir), result.screenshot);
await writeFile(new URL("example.html", outDir), result.dom, "utf8");
await writeFile(new URL("capture.json", outDir), JSON.stringify({
  target: result.target,
  viewport: result.viewport,
  title: result.title,
  consoleMessages: result.consoleMessages,
  failedRequests: result.failedRequests,
  capturedAt: result.capturedAt
}, null, 2));

console.log(JSON.stringify({ ok: true, title: result.title, screenshotBytes: result.screenshot.length, consoleCount: result.consoleMessages.length, failedRequestCount: result.failedRequests.length }));
