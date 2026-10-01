/**
 * Durable Provider boundary.
 *
 * This interface is intentionally provider-neutral. It may later be backed by
 * a real database/queue without changing the Master Agent or orchestration
 * kernel. Until a verified provider is bound, every operation is fail-closed.
 */
export function createUnboundDurableProvider(){
 return {
  name:null,
  available:false,
  productionAuthority:false,
  async get(){return null},
  async put(){throw durableProviderError()},
  async appendAudit(){throw durableProviderError()},
  async transaction(){throw durableProviderError()}
 };
}
export function durableProviderError(){
 const e=new Error("durable_provider_not_bound");
 e.code="DURABLE_PROVIDER_NOT_BOUND";
 return e;
}
export function isDurableProvider(value){
 return Boolean(value&&value.available===true&&value.productionAuthority===true&&typeof value.get==="function"&&typeof value.put==="function"&&typeof value.appendAudit==="function"&&typeof value.transaction==="function");
}
