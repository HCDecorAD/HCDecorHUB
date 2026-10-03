export class ReleaseGate{
  evaluate({tests,findings=[]}){
    if(!tests?.ok) return {status:"BLOCKED",reason:"TESTS_NOT_GREEN"};
    const critical=findings.filter(x=>x.state!=="RESOLVED"&&x.severity==="critical");
    if(critical.length) return {status:"BLOCKED",reason:"UNRESOLVED_CRITICAL",count:critical.length};
    const high=findings.filter(x=>x.state!=="RESOLVED"&&x.severity==="high");
    return {status:high.length?"REVIEW_REQUIRED":"PASS",openHigh:high.length};
  }
}
