import {NotificationCenter} from "../core/notification-center.js";import {BaselinePromotion} from "../core/baseline-promotion.js";import {WatchRuntime} from "../core/watch-runtime.js";
const n=new NotificationCenter();const bp=new BaselinePromotion();bp.propose({id:"gsc-next",revision:"r2"});bp.review("gsc-next");bp.approve("gsc-next",{reviewer:"operator"});
const w=new WatchRuntime({notifications:n,runTarget:async()=>({findings:[{severity:"medium"}]})});w.register({id:"gsc"},{intervalMs:10});const watched=await w.tick(10);
const result={baseline:bp.items.get("gsc-next").state,watchRuns:watched.length,alerts:n.unread().length};console.log(JSON.stringify(result));if(result.baseline!=="APPROVED"||result.watchRuns!==1||result.alerts!==1)process.exit(2);
