import {mkdir,writeFile} from "node:fs/promises";
import {resolve} from "node:path";
import {parseOperatorCommand} from "../operator/command-parser.js";

export function createLiveController({projects,targets,observer,inspectSnapshot,evidence,findings,logs,evidenceDir}){
  return {
    async run(input){
      const command=parseOperatorCommand(input,projects);
      if(!command.projectId)return {status:"REVIEW_REQUIRED",reason:"PROJECT_NOT_RESOLVED",command};
      const target=targets.find(x=>x.enabled!==false&&x.projectId===command.projectId);
      if(!target)return {status:"REVIEW_REQUIRED",reason:"TARGET_NOT_RESOLVED",command};
      const viewportName=command.viewport==="default"?"desktop":command.viewport;
      const viewport=target.viewports?.[viewportName]??target.viewports?.desktop??{width:1440,height:900};
      await logs?.append?.({level:"info",type:"MISSION_START",message:`${command.action} ${target.id} ${viewportName}`});
      try{
        const capture=await observer.capture({url:target.url,waitUntil:"domcontentloaded",timeoutMs:45000},{viewport,fullPage:false});
        const qualityFindings=inspectSnapshot({dom:capture.dom,failedRequests:capture.failedRequests,viewport});
        await mkdir(evidenceDir,{recursive:true});
        const stamp=new Date().toISOString().replace(/[:.]/g,"-");
        const screenshotPath=resolve(evidenceDir,`${target.id}-${viewportName}-${stamp}.png`);
        await writeFile(screenshotPath,capture.screenshot);
        const evidenceId=`${target.id}-${viewportName}-${stamp}`;
        await evidence.add({
          id:evidenceId,projectId:target.projectId,targetId:target.id,type:"screenshot",
          path:screenshotPath,title:capture.title,viewport:viewportName,
          consoleCount:capture.consoleMessages.length,failedRequestCount:capture.failedRequests.length,
          findings:qualityFindings
        });
        for(const f of qualityFindings){
          await findings.upsert({
            id:`${target.projectId}-${f.kind.toLowerCase()}`,projectId:target.projectId,targetId:target.id,
            state:"ROUTED",severity:f.severity,kind:f.kind,count:f.count??1,evidenceId
          });
        }
        const status=qualityFindings.length?"ROUTED":"DONE";
        await logs?.append?.({level:qualityFindings.length?"warning":"info",type:"MISSION_COMPLETE",message:`${target.id} ${status}`});
        return {
          status,projectId:target.projectId,targetId:target.id,viewport:viewportName,
          evidenceId,findings:qualityFindings,failedRequestCount:capture.failedRequests.length,
          consoleCount:capture.consoleMessages.length,title:capture.title
        };
      }catch(e){
        await logs?.append?.({level:"error",type:"MISSION_ERROR",message:String(e.message||e)});
        return {status:e.code==="WAITING_CAPABILITY"?"WAITING_CAPABILITY":"BLOCKED",error:String(e.message||e),command};
      }
    }
  };
}
