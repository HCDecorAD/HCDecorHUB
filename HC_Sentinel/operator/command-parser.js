const ACTIONS = [
  ["kiem tra","CHECK"],["kiểm tra","CHECK"],["soi loi","CHECK"],["soi lỗi","CHECK"],
  ["so sanh","COMPARE"],["so sánh","COMPARE"],["verify","VERIFY"],["kiem chung","VERIFY"],["kiểm chứng","VERIFY"]
];

export function parseOperatorCommand(input, registry) {
  const raw=String(input||"").trim();
  if(!raw) throw new Error("EMPTY_COMMAND");
  const normalized=raw.toLowerCase();
  const action=(ACTIONS.find(([k])=>normalized.includes(k))||[null,"CHECK"])[1];
  const projects=registry?.all?.() ?? [];
  const project=projects.find(p=>normalized.includes(String(p.id).toLowerCase()) || normalized.includes(String(p.name||"").toLowerCase())) ?? null;
  const viewport=normalized.includes("mobile") ? "mobile" : normalized.includes("tablet") ? "tablet" : normalized.includes("desktop") ? "desktop" : "default";
  return { raw, action, projectId:project?.id ?? null, viewport };
}
