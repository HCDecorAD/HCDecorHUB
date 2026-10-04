export function socialHandoff(pack={},winner={}){
 if(winner.status!=="SCALE"&&winner.status!=="VARIANT_TEST")return {status:"BLOCKED_NO_WINNER",jobs:[]};
 const items=Array.isArray(pack.items)?pack.items:[];
 const jobs=items.map(x=>({content_id:x.id,platform:x.platform,status:"READY_FOR_EXISTING_PUBLISHING",requires_account:true,requires_schedule:true,provenance:x.provenance||[]}));
 return {status:jobs.length?"READY":"EMPTY",jobs,execution_engine:"EXISTING_SOCIAL_PUBLISHING",publish:false};
}