import {readFile,mkdir,writeFile} from "node:fs/promises";import {PlaywrightObserver} from "../adapters/browser/playwright-observer.js";import {inspectSnapshot} from "../core/quality.js";
const cfg=JSON.parse(await readFile(new URL("../data/targets.json",import.meta.url),"utf8"));
const observer=new PlaywrightObserver();const out=[];
await mkdir(new URL("../evidence/p19/",import.meta.url),{recursive:true});
for(const target of cfg.targets.filter(x=>x.enabled)){
  try{
    const viewport=target.viewports.desktop;
    const r=await observer.capture({url:target.url,waitUntil:"domcontentloaded",timeoutMs:45000},{viewport,fullPage:false});
    const findings=inspectSnapshot({dom:r.dom,failedRequests:r.failedRequests,viewport});
    const item={id:target.id,url:target.url,ok:true,title:r.title,consoleCount:r.consoleMessages.length,failedRequestCount:r.failedRequests.length,qualityFindings:findings};
    await writeFile(new URL(`../evidence/p19/${target.id}.png`,import.meta.url),r.screenshot);
    await writeFile(new URL(`../evidence/p19/${target.id}.json`,import.meta.url),JSON.stringify(item,null,2));
    out.push(item);
  }catch(e){out.push({id:target.id,url:target.url,ok:false,error:String(e.message||e)});}
}
await writeFile(new URL("../evidence/p19/summary.json",import.meta.url),JSON.stringify(out,null,2));
console.log(JSON.stringify(out));
if(out.some(x=>!x.ok)) process.exit(2);
