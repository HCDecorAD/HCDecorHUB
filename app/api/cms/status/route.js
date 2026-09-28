import {readWordPressSite,wordpressWriteCapability} from "../../../../lib/cms/wordpress";
export async function GET(){
 const checkedAt=new Date().toISOString();
 try{const site=await readWordPressSite();return Response.json({provider:"wordpress.com",read:site,write:wordpressWriteCapability(),checkedAt},{status:site.ok?200:502})}
 catch{return Response.json({provider:"wordpress.com",status:"error",checkedAt},{status:502})}
}
