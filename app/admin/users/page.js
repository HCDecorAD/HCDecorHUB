"use client";
import {useEffect,useState} from "react";
// SuperAdmin only (the API enforces it). A generated password is shown once and kept only in this component state.
export default function Users(){
 const [users,setUsers]=useState(null),[once,setOnce]=useState(null),[err,setErr]=useState("");
 async function load(){
  const r=await fetch("/api/admin/users");
  if(!r.ok){setUsers([]);setErr("Chỉ SuperAdmin được truy cập.");return}
  setUsers((await r.json()).users);
 }
 async function call(payload){
  setErr("");
  const r=await fetch("/api/admin/users",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify(payload)});
  const d=await r.json().catch(()=>({}));
  if(!r.ok){setErr("Không thực hiện được: "+(d.error||r.status));return}
  if(d.password)setOnce({id:d.user.id,password:d.password});
  load();
 }
 useEffect(()=>{load()},[]);
 function create(e){
  e.preventDefault();
  const f=new FormData(e.target);
  call({action:"create",username:f.get("username"),role:f.get("role"),site_ids:String(f.get("sites")||"").split(",")});
 }
 function sites(u){
  const v=prompt("site_ids (cách nhau bằng dấu phẩy)",u.site_ids.join(","));
  if(v!==null)call({action:"update",username:u.id,site_ids:v.split(",")});
 }
 return <main className="admin"><h1>Quản lý người dùng</h1>
 {err&&<p role="alert">{err}</p>}
 {once&&<p role="status">Mật khẩu của <b>{once.id}</b> (chỉ hiện một lần): <code>{once.password}</code> <button onClick={()=>setOnce(null)}>Ẩn</button></p>}
 <form onSubmit={create}><input name="username" placeholder="Tên đăng nhập" required/> <select name="role"><option value="staff">NV</option><option value="guest">Khách</option></select> <input name="sites" placeholder="site_ids: amo,gsc"/> <button className="btn primary" type="submit">Tạo</button></form>
 <table><thead><tr><th>ID</th><th>Vai trò</th><th>Site</th><th>Trạng thái</th><th/></tr></thead><tbody>
 {(users||[]).map(u=><tr key={u.id}><td>{u.id}</td><td>{u.role}</td><td>{u.site_ids.join(", ")}</td><td>{u.disabled?"Khóa":u.must_change_password?"Chờ đổi Pass":"Hoạt động"}</td>
 <td><button onClick={()=>call({action:"reset",username:u.id})}>Đặt lại Pass</button> <button onClick={()=>call({action:"update",username:u.id,disabled:!u.disabled})}>{u.disabled?"Mở khóa":"Khóa"}</button> <button onClick={()=>sites(u)}>Sửa site</button></td></tr>)}
 </tbody></table></main>;
}
