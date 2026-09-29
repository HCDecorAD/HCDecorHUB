import {readFileSync} from "node:fs";
import {authorityIds} from "../data-authorities";
const authority=authorityIds();
export const CRM={sheetId:process.env.HCDECOR_CRM_SHEET_ID||authority.crmSheetId||"",leadsTab:"Leads",projectsTab:"Projects",projectsFolderId:process.env.HCDECOR_DRIVE_PROJECTS_FOLDER_ID||authority.projectsFolderId||""};
export function googleCredentialsConfigured(){if(process.env.GOOGLE_SERVICE_ACCOUNT_JSON)return true;const p=(process.env.GOOGLE_APPLICATION_CREDENTIALS||"").trim();if(!p)return false;try{return Boolean(readFileSync(p,"utf8").trim())}catch{return false}}
export function crmRuntime(){const provider=process.env.HCDECOR_CRM_WRITE_PROVIDER||"",credentials=googleCredentialsConfigured(),projectApiAuthConfigured=Boolean((process.env.HCDECOR_PROJECT_API_TOKEN||"").trim());return {provider,configured:Boolean(CRM.sheetId),writeEnabled:provider==="google-sheets-api"&&credentials&&Boolean(CRM.sheetId),projectApiAuthConfigured,projectProvisionEnabled:provider==="google-sheets-api"&&credentials&&Boolean(CRM.sheetId)&&Boolean(CRM.projectsFolderId)&&projectApiAuthConfigured}}
