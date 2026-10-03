export class AxeAdapter {
  async audit(page) {
    let axe;
    try {
      const mod = await import("axe-core");
      axe = mod.default ?? mod;
    } catch {
      const e=new Error("AXE_UNAVAILABLE");
      e.code="WAITING_CAPABILITY";
      throw e;
    }
    if (!axe?.source) throw new Error("AXE_SOURCE_UNAVAILABLE");
    await page.addScriptTag({ content: axe.source });
    return await page.evaluate(async () => {
      const r = await window.axe.run(document);
      return {
        violations: r.violations.map(v => ({ id:v.id, impact:v.impact, nodes:v.nodes.length })),
        incomplete: r.incomplete.map(v => ({ id:v.id, impact:v.impact, nodes:v.nodes.length })),
        passes: r.passes.length
      };
    });
  }
}
