import { BrowserObserver } from "./observer-contract.js";

export class PlaywrightObserver extends BrowserObserver {
  constructor({ browserType = "chromium" } = {}) {
    super();
    this.browserType = browserType;
  }

  async capture(target, options = {}) {
    const { viewport = { width: 1440, height: 900 }, fullPage = true } = options;
    let playwright;
    try {
      playwright = await import("playwright");
    } catch {
      const err = new Error("PLAYWRIGHT_UNAVAILABLE");
      err.code = "WAITING_CAPABILITY";
      throw err;
    }

    const launcher = playwright[this.browserType];
    if (!launcher) throw new Error(`unsupported browserType: ${this.browserType}`);

    const browser = await launcher.launch({ headless: true });
    try {
      const page = await browser.newPage({ viewport });
      const consoleMessages = [];
      const failedRequests = [];
      page.on("console", msg => consoleMessages.push({ type: msg.type(), text: msg.text() }));
      page.on("requestfailed", req => failedRequests.push({ url: req.url(), error: req.failure()?.errorText ?? "unknown" }));
      await page.goto(target.url, { waitUntil: target.waitUntil ?? "networkidle", timeout: target.timeoutMs ?? 30000 });
      const screenshot = await page.screenshot({ fullPage });
      const dom = await page.content();
      return {
        target: target.url,
        viewport,
        screenshot,
        dom,
        consoleMessages,
        failedRequests,
        title: await page.title(),
        capturedAt: new Date().toISOString()
      };
    } finally {
      await browser.close();
    }
  }
}
