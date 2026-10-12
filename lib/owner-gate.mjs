import crypto from "node:crypto";
// Fail-closed owner gate. Bearer token from env HCDECOR_OWNER_API_TOKEN (32-512 chars); unset => always denied.
export function isOwnerRequest(authorizationHeader,expectedRaw=process.env.HCDECOR_OWNER_API_TOKEN){
  const expected=(expectedRaw||"").trim(),header=authorizationHeader||"";
  if(expected.length<32||expected.length>512||!header.startsWith("Bearer "))return false;
  const supplied=Buffer.from(header.slice(7)),exp=Buffer.from(expected);
  return supplied.length===exp.length&&crypto.timingSafeEqual(supplied,exp);
}
