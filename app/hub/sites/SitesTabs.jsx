"use client";
import {useState} from "react";
// Hai tab trên SuperAdmin: "Sites" (chỉ đọc) và "Quản lý" (tạo ID/Pass). Cùng một trang HUB, không tách URL.
export default function SitesTabs({sitesPanel,managePanel}){
 const [tab,setTab]=useState("sites");
 return <>
  <div className="tabs" role="tablist">
   <button role="tab" aria-selected={tab==="sites"} className={tab==="sites"?"btn primary":"btn"} onClick={()=>setTab("sites")}>Sites</button>
   <button role="tab" aria-selected={tab==="manage"} className={tab==="manage"?"btn primary":"btn"} onClick={()=>setTab("manage")}>Quản lý</button>
  </div>
  {tab==="sites"?sitesPanel:managePanel}
 </>;
}
