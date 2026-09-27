export async function GET(){
 const checks={hub:{ok:true,mode:"v2"},cms:{ok:false,mode:"readonly-demo"},drive:{ok:Boolean(process.env.HCDECOR_DRIVE_ROOT_FOLDER_ID),mode:"configured"},agent:{ok:true,mode:process.env.HCDECOR_AGENT_BASE_URL?"external":"demo"},social:{ok:false,mode:"deferred"}};
 try{const base=process.env.HCDECOR_WP_BASE_URL;if(base){const r=await fetch(base.replace(/\/$/,"")+"/wp-json/",{cache:"no-store"});checks.cms.ok=r.ok;checks.cms.http=r.status}}catch{}
 return Response.json({ok:checks.hub.ok,version:"HCDecor HUB V2",projectIdFormat:"HC-YYYY-XXXX",checks,checkedAt:new Date().toISOString()});
}