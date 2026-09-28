import fs from "node:fs";
import path from "node:path";
const read=name=>JSON.parse(fs.readFileSync(path.join(process.cwd(),"config",name),"utf8"));
export function getPolicy(){return {permissions:read("permissions.json"),commandCenter:read("command-center.json")}}
export function productionGuard(){const {permissions,commandCenter}=getPolicy();return {guarded:Object.keys(commandCenter.production_guard).filter(k=>commandCenter.production_guard[k]),restricted:permissions.restricted_actions,rule:permissions.rule,flow:commandCenter.command_flow}}
