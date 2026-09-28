import {readWordPressSite,wordpressWriteCapability} from "../../../../lib/cms/wordpress";
export async function GET(){
 const checkedAt=new Date().toISOString();
 const write=wordpressWriteCapability();
 try{
  const site=await readWordPressSite();
  const liveHealth=site.ok?"healthy":(site.status==="not_configured"?"not_checked":"unreachable");
  return Response.json({provider:"wordpress",config:{read:write.configured,writeCredential:write.credentialPresent,writeMode:write.mode},liveHealth,probe:site,checkedAt},{status:site.ok?200:(site.status==="not_configured"?503:502)});
 }catch{
  return Response.json({provider:"wordpress",config:{read:write.configured,writeCredential:write.credentialPresent,writeMode:write.mode},liveHealth:"unreachable",checkedAt},{status:502});
 }
}
