import {mkdir,writeFile,readFile} from "node:fs/promises";
import {resolve} from "node:path";
import {createHash} from "node:crypto";
import {parseOperatorCommand} from "../operator/command-parser.js";

const digest=b=>createHash("sha256").update(b).digest("hex");

export function createLiveController({projects,getTargets,targets:staticTargets,observer,inspectSnapshot,evidence,findings,logs,evidenceDir,repairQueue,tray}){
  const targets=async()=>typeof getTargets==="function"?await getTargets():(staticTargets??[]);
  async function resolveMission(input){
    const command=parseOperatorCommand(input,projects);
    if(!command.projectId)return {error:{status:"REVIEW_REQUIRED",reason:"PROJECT_NOT_RESOLVED",command}};
    const list=await targets();
    const target=list.find(x=>x.enabled!==false&&x.projectId===command.projectId);
    if(!target)return {error:{status:"REVIEW_REQUIRED",reason:"TARGET_NOT_RESOLVED",command}};
    const viewportName=command.viewport==="default"?"desktop":command.viewport;
    const viewport=target.viewports?.[viewportName]??target.viewports?.desktop??{width:1440,height:900};
    return {command,target,viewportName,viewport};
  }
  async function captureMission(input,mode="CHECK"){
    const resolved=await resolveMission(input);if(resolved.error)return resolved.error;
    const {command,target,viewportName,viewport}=resolved;
    const prior=(await evidence.list({projectId:target.projectId,type:"screenshot"})).filter(x=>x.targetId===target.id&&x.viewport===viewportName).at(-1)??null;
    await logs?.append?.({level:"info",type:"MISSION_START",message:`${mode} ${target.id} ${viewportName}`});
    try{
      const capture=await observer.capture({url:target.url,waitUntil:"domcontentloaded",timeoutMs:45000},{viewport,fullPage:false});
      const detectedFindings=inspectSnapshot({dom:capture.dom,failedRequests:capture.failedRequests,viewport});
      const suppressedKinds=new Set(target.suppressFindings??[]);
      const suppressedFindings=detectedFindings.filter(f=>suppressedKinds.has(f.kind));
      const qualityFindings=detectedFindings.filter(f=>!suppressedKinds.has(f.kind));
      await mkdir(evidenceDir,{recursive:true});
      const stamp=new Date().toISOString().replace(/[:.]/g,"-");
      const screenshotPath=resolve(evidenceDir,`${target.id}-${viewportName}-${stamp}.png`);
      await writeFile(screenshotPath,capture.screenshot);
      const evidenceId=`${target.id}-${viewportName}-${stamp}`;
      let comparison=null;
      if(mode==="COMPARE"){
        if(prior?.path){
          try{
            const previous=await readFile(prior.path);
            comparison={baselineEvidenceId:prior.id,changed:digest(previous)!==digest(capture.screenshot),previousHash:digest(previous),currentHash:digest(capture.screenshot)};
          }catch{comparison={baselineEvidenceId:prior.id,changed:null,reason:"BASELINE_FILE_UNAVAILABLE"};}
        }else comparison={changed:null,reason:"NO_PRIOR_EVIDENCE"};
      }
      await evidence.add({id:evidenceId,projectId:target.projectId,targetId:target.id,type:"screenshot",path:screenshotPath,title:capture.title,viewport:viewportName,consoleCount:capture.consoleMessages.length,failedRequestCount:capture.failedRequests.length,findings:qualityFindings,suppressedFindings,comparison});
      for(const f of qualityFindings){
        await findings.upsert({id:`${target.projectId}-${f.kind.toLowerCase()}`,projectId:target.projectId,targetId:target.id,state:"ROUTED",severity:f.severity,kind:f.kind,count:f.count??1,evidenceId});
      }
      let status=qualityFindings.length?"ROUTED":"DONE";
      if(mode==="VERIFY"&&!qualityFindings.length)status="VERIFIED";
      if(mode==="COMPARE"&&!qualityFindings.length)status=comparison?.changed===true?"CHANGED":comparison?.changed===false?"SAME":"DONE";
      await logs?.append?.({level:qualityFindings.length?"warning":"info",type:"MISSION_COMPLETE",message:`${target.id} ${mode} ${status}`});
      await tray?.send?.({title:`HC Sentinel — ${target.name??target.id}`,message:`${mode}: ${status}`,level:qualityFindings.length?"warning":"info"});
      return {status,action:mode,projectId:target.projectId,targetId:target.id,viewport:viewportName,evidenceId,findings:qualityFindings,suppressedFindings,comparison,failedRequestCount:capture.failedRequests.length,consoleCount:capture.consoleMessages.length,title:capture.title};
    }catch(e){
      await logs?.append?.({level:"error",type:"MISSION_ERROR",message:String(e.message||e)});
      await tray?.send?.({title:"HC Sentinel",message:`${mode} failed: ${String(e.message||e)}`,level:"error"});
      return {status:e.code==="WAITING_CAPABILITY"?"WAITING_CAPABILITY":"BLOCKED",error:String(e.message||e),command};
    }
  }
  return {
    run:input=>captureMission(input,"CHECK"),
    action:async(action,input)=>{
      const mode=String(action||"CHECK").toUpperCase();
      if(["CHECK","COMPARE","VERIFY"].includes(mode))return captureMission(input,mode);
      if(mode==="REPAIR"){
        const resolved=await resolveMission(input);if(resolved.error)return resolved.error;
        const {target,viewportName}=resolved;
        const all=await findings.list({projectId:target.projectId});
        const finding=[...all].reverse().find(x=>["ROUTED","OPEN","ACKNOWLEDGED"].includes(x.state));
        if(!finding)return {status:"NO_CHANGE",action:"REPAIR",reason:"NO_ACTIVE_FINDING",projectId:target.projectId};
        const job=await repairQueue.add({projectId:target.projectId,targetId:target.id,findingId:finding.id,kind:finding.kind??"FINDING",severity:finding.severity??"medium",viewport:viewportName,evidenceId:finding.evidenceId??null});
        await logs?.append?.({level:"warning",type:"REPAIR_ROUTED",message:`${job.id} ${target.id} ${finding.id}`});
        await tray?.send?.({title:`Repair routed — ${target.name??target.id}`,message:`${finding.kind??finding.id} → ${job.id}`,level:"warning"});
        return {status:"ROUTED",action:"REPAIR",job};
      }
      return {status:"REVIEW_REQUIRED",reason:"UNKNOWN_ACTION",action:mode};
    }
  };
}
