import {mkdtemp,rm} from "node:fs/promises";import {tmpdir} from "node:os";import {join} from "node:path";import {JsonStore} from "../core/json-store.js";import {FindingStore} from "../core/finding-store.js";import {mapHcdrIssueState} from "../adapters/repair/hdcr-status-sync.js";
const d=await mkdtemp(join(tmpdir(),"sentinel-p35-39-"));
try{
 const store=new FindingStore(new JsonStore(join(d,"findings.json")));
 const mapped=mapHcdrIssueState({state:"open",comments:0});
 await store.upsert({id:"gsc-1265",projectId:"gsc",state:mapped.state,externalIssue:1265});
 const items=await store.list({projectId:"gsc"});
 const result={tracked:items.length,state:items[0].state,issue:items[0].externalIssue};
 console.log(JSON.stringify(result));
 if(result.tracked!==1||result.state!=="ROUTED"||result.issue!==1265)process.exit(2);
}finally{await rm(d,{recursive:true,force:true});}
