import {getPolicy} from "./hub-policy";
import {durableExecutionAvailable} from "./durable-runtime";
import {evaluatePolicyCore,isMutationCore} from "./policy-engine-core.mjs";

export function evaluatePolicy(input={}){
 const {permissions}=getPolicy();
 return evaluatePolicyCore({permissions,durable_execution_available:durableExecutionAvailable()},input);
}
export function isMutation(action){return isMutationCore(action)}
