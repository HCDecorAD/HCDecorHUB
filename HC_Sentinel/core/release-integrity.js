import {createHash} from "node:crypto";
export function releaseDigest(payload){return createHash("sha256").update(JSON.stringify(payload)).digest("hex");}
export function verifyReleaseDigest(payload,digest){return releaseDigest(payload)===digest;}
