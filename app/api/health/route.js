export async function GET(){return Response.json({service:"hcdecor-hub",status:"ok",wordpress_configured:Boolean(process.env.HCDECOR_WP_BASE_URL),time:new Date().toISOString()})}
