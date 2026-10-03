export function correlationFromJob(body={}){
  const ids={};
  for(const k of ["source_id","mission_id","correlation_id"]){
    const v=body?.[k];
    if(typeof v==="string" && v.trim()) ids[k]=v.trim();
  }
  return ids;
}
export function resultEnvelope({job,body,result}){
  return {schema:"hcdr-result/v2",job,...correlationFromJob(body),...result};
}
