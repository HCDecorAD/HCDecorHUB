export class LeaseRegistry {
  constructor({ now=()=>Date.now() }={}) { this.now=now; this.leases=new Map(); }
  claim(key, owner, ttlMs=30000) {
    const cur=this.leases.get(key);
    if (cur && cur.expiresAt>this.now() && cur.owner!==owner) return false;
    this.leases.set(key,{owner,expiresAt:this.now()+ttlMs});
    return true;
  }
  heartbeat(key, owner, ttlMs=30000) {
    const cur=this.leases.get(key);
    if (!cur || cur.owner!==owner) return false;
    cur.expiresAt=this.now()+ttlMs; return true;
  }
  stale(key) { const cur=this.leases.get(key); return !cur || cur.expiresAt<=this.now(); }
}
