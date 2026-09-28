const BASE="https://hcdecorhub.com";
export async function GET(){return Response.json({ok:true,base:BASE,checkedAt:new Date().toISOString(),mode:"registry-target-only",note:"HCDecor production target is WordPress. This endpoint does not claim live route health without an explicit probe."})}
