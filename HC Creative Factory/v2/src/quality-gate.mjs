export const REQUIRED=["capture_http_200","rendered_html","screenshot","visual_diff_real","interaction_qa","responsive_qa","rollback_authority"];
export function qualityGate(evidence){const checks=Object.fromEntries(REQUIRED.map(k=>[k,Boolean(evidence[k])]));return {stage:"V2.9",status:Object.values(checks).every(Boolean)?"PASS":"FAIL",checks}}
