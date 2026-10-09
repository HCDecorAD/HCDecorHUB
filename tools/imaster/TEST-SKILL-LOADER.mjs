import {loadHcdrSkill} from "../hcdr-relay/skill-loader.mjs";
import {resolve} from "node:path";
const result=await loadHcdrSkill(resolve(import.meta.dirname,"../../"));
console.log(JSON.stringify(result));
if(!result.loaded||!result.contract_valid)process.exitCode=43;
