import crypto from 'node:crypto';

const OUTCOMES=new Set(['success','failure','waiting','blocked','info']);

export function createEvidenceEvent({mission_id,correlation_id=null,event_type,component,outcome='info',checkpoint=null,parent_event_id=null,attributes={}}={}){
  if(!mission_id) throw new Error('MISSION_ID_REQUIRED');
  if(!event_type) throw new Error('EVENT_TYPE_REQUIRED');
  if(!component) throw new Error('COMPONENT_REQUIRED');
  if(!OUTCOMES.has(outcome)) throw new Error('INVALID_OUTCOME');
  const safeAttributes={};
  for(const [k,v] of Object.entries(attributes||{})){
    if(/token|secret|password|authorization|cookie/i.test(k)) continue;
    safeAttributes[k]=v;
  }
  const correlation=correlation_id||mission_id;
  return {
    schema_version:'1.0.0',
    event_id:crypto.randomUUID(),
    mission_id:String(mission_id),
    correlation_id:String(correlation),
    parent_event_id:parent_event_id?String(parent_event_id):null,
    event_type:String(event_type),
    component:String(component),
    outcome,
    timestamp:new Date().toISOString(),
    checkpoint:checkpoint??null,
    attributes:safeAttributes
  };
}

export function validateEvidenceEvent(e){
  if(!e||e.schema_version!=='1.0.0') return {ok:false,error:'invalid_schema_version'};
  for(const k of ['event_id','mission_id','correlation_id','event_type','component','outcome','timestamp']) if(!e[k]) return {ok:false,error:'missing_'+k};
  if(!OUTCOMES.has(e.outcome)) return {ok:false,error:'invalid_outcome'};
  if(Number.isNaN(Date.parse(e.timestamp))) return {ok:false,error:'invalid_timestamp'};
  if(Object.keys(e.attributes||{}).some(k=>/token|secret|password|authorization|cookie/i.test(k))) return {ok:false,error:'sensitive_attribute'};
  return {ok:true};
}
