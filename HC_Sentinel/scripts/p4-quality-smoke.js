import { chromium } from "playwright";
import { AxeAdapter } from "../adapters/accessibility/axe-adapter.js";
import { mkdir, writeFile } from "node:fs/promises";

const outDir=new URL("../evidence/p4/",import.meta.url);
await mkdir(outDir,{recursive:true});
const browser=await chromium.launch({headless:true});
try{
  const page=await browser.newPage({viewport:{width:390,height:844}});
  await page.setContent(`<!doctype html><html><head><title>Sentinel Fixture</title></head><body><img src=""><button></button><div style="width:1200px">wide</div></body></html>`);
  const axe=new AxeAdapter();
  const audit=await axe.audit(page);
  const bodyWidth=await page.evaluate(()=>({scrollWidth:document.documentElement.scrollWidth,clientWidth:document.documentElement.clientWidth}));
  const result={audit,bodyWidth};
  await writeFile(new URL("quality.json",outDir),JSON.stringify(result,null,2));
  console.log(JSON.stringify({ok:true,violations:audit.violations.length,incomplete:audit.incomplete.length,overflow:bodyWidth.scrollWidth>bodyWidth.clientWidth}));
  if(audit.violations.length<1) process.exit(2);
} finally { await browser.close(); }
