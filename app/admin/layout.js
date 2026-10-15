import {notFound} from "next/navigation";
import {getPrincipal,canViewSuperAdmin} from "../../lib/superadmin";

export const dynamic="force-dynamic";

// Khu /admin (cấu hình, chẩn đoán, CRM, release) chỉ dành cho SuperAdmin.
// Khách hoặc người chưa đăng nhập GitHub thấy 404, giống trang /hub/sites.
export default async function AdminLayout({children}){
 if(!canViewSuperAdmin(await getPrincipal()))notFound();
 return children;
}
