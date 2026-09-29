import {readWordPressSite,wordpressWriteCapability,probeHCDecorRuntime} from "../../../../lib/cms/wordpress";
export async function GET(){
 const checkedAt=new Date().toISOString(),write=wordpressWriteCapability();
 try{
  const [site,runtimePlugin]=await Promise.all([readWordPressSite(),probeHCDecorRuntime()]);
  const liveHealth=site.ok?"healthy":(site.status==="not_configured"?"not_checked":"unreachable");
  return Response.json({provider:"wordpress",config:{read:write.configured,writeCredential:write.credentialPresent,writeMode:write.mode},liveHealth,probe:site,runtimePlugin,sourceProductionDrift:site.ok&&!runtimePlugin.healthy,production_write:false,checkedAt},{status:site.ok?200:(site.status==="not_configured"?503:502)});
 }catch{
  return Response.json({provider:"wordpress",config:{read:write.configured,writeCredential:write.credentialPresent,writeMode:write.mode},liveHealth:"unreachable",production_write:false,checkedAt},{status:502});
 }
}
