export class FindingRouter {
  route(finding) {
    if (!finding) return { status:"REVIEW_REQUIRED", lane:"review" };
    if (finding.confidence != null && finding.confidence < 0.6) return { status:"REVIEW_REQUIRED", lane:"review" };
    if (finding.kind==="NETWORK_FAILURE") return { status:"ROUTED", lane:"autodebug", capability:"network-debug" };
    if (finding.kind==="VISUAL_DIFF") return { status:"ROUTED", lane:"visual-repair", capability:"ui-repair" };
    if (finding.kind==="ACCESSIBILITY") return { status:"ROUTED", lane:"quality-repair", capability:"a11y-repair" };
    return { status:"ROUTED", lane:"project-worker", capability:"generic-repair" };
  }
}
