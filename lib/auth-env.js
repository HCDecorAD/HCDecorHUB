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
