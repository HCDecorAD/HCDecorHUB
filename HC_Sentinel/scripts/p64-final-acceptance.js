import {AlertDedupe} from "../core/alert-dedupe.js";import {evaluateUpdate} from "../core/update-policy.js";import {checkResourceBudget} from "../core/resource-budget.js";import {releaseDigest,verifyReleaseDigest} from "../core/release-integrity.js";
let now=0;const dedupe=new AlertDedupe({windowMs:100,now:()=>now});const first=dedupe.accept("gsc-medium");now=50;const second=dedupe.accept("gsc-medium");now=101;const third=dedupe.accept("gsc-medium");
const update=evaluateUpdate({current:"1.1.0",candidate:"1.2.0",testsGreen:true,criticalOpen:0});
const budget=checkResourceBudget({cpuPercent:30,memoryPercent:40,queueDepth:2});
const payload={version:"1.2.0",gate:"FINAL_OPERATIONS_READY"};const digest=releaseDigest(payload);
const result={alerts:[first,second,third],update:update.status,budget:budget.status,integrity:verifyReleaseDigest(payload,digest)};
console.log(JSON.stringify(result));
if(JSON.stringify(result.alerts)!=="[true,false,true]"||result.update!=="READY"||result.budget!=="OK"||!result.integrity)process.exit(2);
