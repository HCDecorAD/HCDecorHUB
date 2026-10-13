import {redirect} from "next/navigation";
// Chuyển về tab "Quản lý" trên HUB, không còn trang quản lý riêng.
export default function Users(){redirect("/hub/sites");}
