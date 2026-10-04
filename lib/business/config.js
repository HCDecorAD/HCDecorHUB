import {CRM,googleCredentialsConfigured} from "../crm/config";

export const BUSINESS={
  sheetId:CRM.sheetId,
  tabs:{customers:"Customers",quotations:"Quotations",payments:"Payments"}
};
export function businessRuntime(){
  const provider=(process.env.HCDECOR_CRM_WRITE_PROVIDER||"").trim();
  const tokenConfigured=Boolean((process.env.HCDECOR_BUSINESS_API_TOKEN||process.env.HCDECOR_PROJECT_API_TOKEN||"").trim());
  const credentials=googleCredentialsConfigured();
  const configured=Boolean(BUSINESS.sheetId);
  return {
    provider,configured,credentials,tokenConfigured,
    writeEnabled:provider==="google-sheets-api"&&credentials&&configured&&tokenConfigured,
    resources:Object.keys(BUSINESS.tabs),
    production_write:false
  };
}
