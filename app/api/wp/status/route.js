const normalizeBaseUrl = (value) => value?.trim().replace(/\/$/, "");
export async function GET() {
  const checkedAt = new Date().toISOString();
  const base = normalizeBaseUrl(process.env.HCDECOR_WP_BASE_URL);
  if (!base) return Response.json({status:"not_configured",reason:"HCDECOR_WP_BASE_URL is not configured",checkedAt},{status:503});
  try {
    const r=await fetch(`${base}/wp-json/`,{cache:"no-store"});
    const data=r.ok?await r.json():null;
    return Response.json({status:r.ok?"connected":"error",http:r.status,backend:base,site:data?.name||null,description:data?.description||null,api:"wordpress-rest",checkedAt},{status:r.ok?200:502});
  } catch {return Response.json({status:"error",reason:"WordPress unreachable",backend:base,checkedAt},{status:502});}
}
