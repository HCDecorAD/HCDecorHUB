export function scaleDecision({winner={},analytics_verified=false,publish_receipts=[]}={}){
 if(!analytics_verified||winner.status!=="SCALE")return {status:"HOLD",reason:"VERIFIED_WINNER_AND_ANALYTICS_REQUIRED"};
 if(!Array.isArray(publish_receipts)||publish_receipts.length<1)return {status:"HOLD",reason:"PUBLISH_RECEIPT_REQUIRED"};
 return {status:"SCALE_READY",actions:["CONTENT_FACTORY_VARIANTS","EXPAND_DISTRIBUTION","VERIFY_ANALYTICS_AGAIN"]};
}