import authorities from "../config/data-authorities.json";
export function getDataAuthorities(){return authorities.authorities||{}}
export function authorityIds(){const a=getDataAuthorities();return {crmSheetId:a.crm?.spreadsheet_id||"",driveRootId:a.drive?.root_folder_id||"",projectsFolderId:a.drive?.projects_folder_id||""}}
