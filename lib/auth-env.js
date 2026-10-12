// Worker (OpenNext) dashboard vars/secrets are bindings, not always in process.env.
// Bindings win; process.env fills gaps (local dev / Node). Failure -> process.env only.
export async function getAuthEnv(){
 let bindings={};
 try{
  const {getCloudflareContext}=await import("@opennextjs/cloudflare");
  const ctx=await getCloudflareContext({async:true});
  bindings=ctx?.env||{};
 }catch{}
 return {...process.env,...Object.fromEntries(Object.entries(bindings).filter(([,v])=>typeof v==="string"))};
}
// Workers KV binding HC_USERS is an object binding, so it is not part of getAuthEnv. Missing -> null (default-deny).
export async function getUsersKv(){
 try{
  const {getCloudflareContext}=await import("@opennextjs/cloudflare");
  const ctx=await getCloudflareContext({async:true});
  return ctx?.env?.HC_USERS||null;
 }catch{return null}
}
