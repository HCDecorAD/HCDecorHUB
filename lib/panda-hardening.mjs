export function shouldRecover({last_seen,event_seen,lease_expired}){return Boolean(!event_seen||lease_expired||last_seen==="CRASHED")}
export function circuit({failures=0,limit=3}){return failures>=limit?{open:true,action:"OWNER_REQUIRED"}:{open:false,action:"CONTINUE"}}
export function dedupe(ledger,key){if(ledger.has(key))return false;ledger.add(key);return true}
