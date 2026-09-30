export function durableRuntimeStatus(){
 return {
  available:false,
  reason:"durable-provider-not-bound",
  provider:null,
  productionAuthority:false
 };
}
export function durableExecutionAvailable(){return durableRuntimeStatus().available===true}
