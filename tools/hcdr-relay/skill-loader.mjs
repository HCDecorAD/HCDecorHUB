import fs from "node:fs/promises";
import path from "node:path";
import crypto from "node:crypto";

export async function loadHcdrSkill(repoRoot){
  const rulePath=path.join(repoRoot,"AGENTS.md");
  const skillPath=path.join(repoRoot,"skills","hcdr-autorecovery","SKILL.md");
  try{
    const [rules,skill]=await Promise.all([fs.readFile(rulePath,"utf8"),fs.readFile(skillPath,"utf8")]);
    if(!rules.includes("skills/hcdr-autorecovery/SKILL.md"))throw Error("RULE_BINDING_MISSING");
    for(const token of ["CHECK","BAT","GITHUB","RETEST","PUBLIC GATE"]){
      if(!skill.includes(token))throw Error("SKILL_CONTRACT_MISSING_"+token.replaceAll(" ","_"));
    }
    return {loaded:true,contract_valid:true,id:"hcdr-autorecovery",sha256:crypto.createHash("sha256").update(skill).digest("hex"),rules_path:"AGENTS.md",skill_path:"skills/hcdr-autorecovery/SKILL.md"};
  }catch(e){
    return {loaded:false,contract_valid:false,id:"hcdr-autorecovery",error_code:e?.code==="ENOENT"?"SKILL_FILE_MISSING":String(e?.message||e).slice(0,100)};
  }
}
