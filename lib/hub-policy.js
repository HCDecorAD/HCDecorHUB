import permissions from "../config/permissions.json";
import commandCenter from "../config/command-center.json";
export function getPolicy(){return {permissions,commandCenter}}
export function productionGuard(){return {guarded:Object.keys(commandCenter.production_guard).filter(k=>commandCenter.production_guard[k]),restricted:permissions.restricted_actions,rule:permissions.rule,flow:commandCenter.command_flow}}
