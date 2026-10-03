import {chromium} from "playwright";import {pathToFileURL} from "node:url";import {resolve} from "node:path";
const browser=await chromium.launch({headless:true});
try{
 const page=await browser.newPage({viewport:{width:1440,height:900}});
 await page.goto(pathToFileURL(resolve("ui/index.html")).href);
 await page.waitForSelector("text=Command Center");
 const initial=await page.evaluate(()=>document.documentElement.dataset.theme);
 await page.click("#themeToggle");
 const toggled=await page.evaluate(()=>document.documentElement.dataset.theme);
 await page.fill("#commandInput","Sentinel kiểm tra GSC mobile");await page.click("#runNow");
 const mission=await page.locator("#missionStatus").innerText();
 console.log(JSON.stringify({initial,toggled,mission}));
 if(initial===toggled||mission!=="RUNNING") process.exit(2);
}finally{await browser.close();}
