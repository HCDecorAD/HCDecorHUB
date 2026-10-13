// Pure helper: who (NV/Khách) may enter each managed site. Input is publicUser rows (no hashes).
export const MANAGED_SITES=["amo","gsc"];
export function usersBySite(users,sites=MANAGED_SITES){
 const out={};
 for(const s of sites){
  out[s]=(users||[]).filter(u=>Array.isArray(u.site_ids)&&u.site_ids.includes(s)).map(u=>({
   id:u.id,role:u.role,
   status:u.disabled?"Khóa":u.must_change_password?"Chờ đổi Pass":"Hoạt động"
  }));
 }
 return out;
}
