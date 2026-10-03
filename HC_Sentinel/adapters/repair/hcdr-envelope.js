export function buildHcdrIssue({projectId,targetId,finding,verification,missionId}){
  if(!projectId||!missionId) throw new Error("PROJECT_AND_MISSION_REQUIRED");
  return {
    title:`[HC Sentinel] Repair ${projectId} — ${missionId}`,
    body:[
      "source: hc-sentinel",
      "",
      `mission_id: ${missionId}`,
      `project_id: ${projectId}`,
      `target_id: ${targetId??"unknown"}`,
      `finding: ${JSON.stringify(finding??{})}`,
      `verification: ${JSON.stringify(verification??{})}`,
      "",
      "RULES",
      "- Do not report DONE without execution evidence.",
      "- Fail closed on uncertain side effects.",
      "- Return changed files, tests, exit codes, and evidence paths."
    ].join("\n")
  };
}
