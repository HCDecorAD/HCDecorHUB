import {scanOutliers} from "../../../../lib/trend-outlier-scanner.mjs";
export async function POST(request){
  try{
    const body=await request.json();
    const videos=Array.isArray(body?.videos)?body.videos:[];
    return Response.json({ok:true,result:scanOutliers(videos,body?.options||{}),production_write:false});
  }catch{return Response.json({ok:false,error:"invalid_json"},{status:400})}
}
