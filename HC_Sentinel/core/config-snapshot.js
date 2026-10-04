export function createConfigSnapshot(config){
  const safe=structuredClone(config??{});
  for(const k of Object.keys(safe)) if(/secret|token|password|cookie|session/i.test(k)) safe[k]="[REDACTED]";
  return {createdAt:new Date().toISOString(),config:safe};
}
