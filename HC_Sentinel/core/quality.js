export function inspectSnapshot({ dom="", failedRequests=[], viewport=null }) {
  const findings=[];
  if (failedRequests.length) findings.push({ kind:"NETWORK_FAILURE", severity:"high", count:failedRequests.length });
  const brokenMedia=(dom.match(/<(img|video)[^>]+(?:src=["']?["']?|src=["']#)/gi)||[]).length;
  if (brokenMedia) findings.push({ kind:"BROKEN_MEDIA_HINT", severity:"medium", count:brokenMedia });
  const overflowHint=/style=["'][^"']*overflow-x\s*:\s*(scroll|auto)/i.test(dom);
  if (overflowHint) findings.push({ kind:"HORIZONTAL_OVERFLOW_HINT", severity:"low", viewport });
  return findings;
}
