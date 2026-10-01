import {createUnboundDurableProvider,isDurableProvider} from "./durable-provider";

let provider=createUnboundDurableProvider();

export function bindDurableProvider(candidate){
 if(!isDurableProvider(candidate))throw new Error("invalid_durable_provider");
 provider=candidate;
 return durableRuntimeStatus();
}
export function getDurableProvider(){return provider}
export function durableRuntimeStatus(){
 return {
  available:provider.available===true,
  reason:provider.available===true?"provider-bound":"durable-provider-not-bound",
  provider:provider.name||null,
  productionAuthority:provider.productionAuthority===true
 };
}
export function durableExecutionAvailable(){
 const s=durableRuntimeStatus();
 return s.available===true&&s.productionAuthority===true;
}
