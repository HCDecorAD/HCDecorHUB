"use client";
import {useEffect,useState} from "react";
// Tab "Quản lý" trên SuperAdmin: tạo ID/Pass, đặt lại Pass, khóa, đổi site. API tự kiểm tra owner.
// Mật khẩu vừa tạo chỉ giữ trong state của component, hiện một lần.
const SITES=["amo","gsc"];
export default function UserManager(){
 const [users,setUsers]=useState(null),[once,setOnce]=useState(null),[err,setErr]=useState("");
 const [form,setForm]=useState({username:"",role:"staff",sites:["amo"]});
 const [editing,setEditing]=useState(null);
 async function load(){
  const r=await fetch("/api/admin/users");
  if(!r.ok){setUsers([]);setErr("Chỉ SuperAdmin được truy cập.");return}
  setUsers((await r.json()).users);
 }
 async function call(payload){
  setErr("");
  const r=await fetch("/api/admin/users",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify(payload)});
  const d=await r.json().catch(()=>({}));
  if(!r.ok){setErr("Không thực hiện được: "+(d.error||r.status));return false}
  if(d.password)setOnce({id:d.user.id,password:d.password});
  await load();
  return true;
 }
 useEffect(()=>{load()},[]);
 function toggleSite(list,sid){return list.includes(sid)?list.filter(x=>x!==sid):[...list,sid]}
 async function create(e){
  e.preventDefault();
  const ok=await call({action:"create",username:form.username,role:form.role,site_ids:form.sites});
  if(ok)setForm({...form,username:""});
 }
 async function saveSites(u){
  await call({action:"update",username:u.id,site_ids:editing.sites});
  setEditing(null);
 }
 return <section className="userManager">
  {err&&<p role="alert">{err}</p>}
  {once&&<p role="status">Mật khẩu của <b>{once.id}</b> (chỉ hiện một lần): <code>{once.password}</code> <button onClick={()=>setOnce(null)}>Ẩn</button></p>}
  <h2>Tạo ID / Pass</h2>
  <form onSubmit={create} className="userForm">
   <input name="username" placeholder="Tên đăng nhập" required value={form.username} onChange={e=>setForm({...form,username:e.target.value})}/>
   <select name="role" value={form.role} onChange={e=>setForm({...form,role:e.target.value})}><option value="staff">NV</option><option value="guest">Khách</option></select>
   {SITES.map(sid=><label key={sid}><input type="checkbox" checked={form.sites.includes(sid)} onChange={()=>setForm({...form,sites:toggleSite(form.sites,sid)})}/> {sid.toUpperCase()}</label>)}
   <button className="btn primary" type="submit">Tạo</button>
  </form>
  <h2>Danh sách tài khoản</h2>
  <table><thead><tr><th>ID</th><th>Vai trò</th><th>Site</th><th>Trạng thái</th><th/></tr></thead><tbody>
  {(users||[]).map(u=><tr key={u.id}>
   <td>{u.id}</td><td>{u.role==="staff"?"NV":"Khách"}</td>
   <td>{editing?.id===u.id?SITES.map(sid=><label key={sid}><input type="checkbox" checked={editing.sites.includes(sid)} onChange={()=>setEditing({...editing,sites:toggleSite(editing.sites,sid)})}/> {sid.toUpperCase()} </label>):u.site_ids.join(", ")}</td>
   <td>{u.disabled?"Khóa":u.must_change_password?"Chờ đổi Pass":"Hoạt động"}</td>
   <td>{editing?.id===u.id?<><button onClick={()=>saveSites(u)}>Lưu site</button> <button onClick={()=>setEditing(null)}>Hủy</button></>:
    <><button onClick={()=>call({action:"reset",username:u.id})}>Đặt lại Pass</button> <button onClick={()=>call({action:"update",username:u.id,disabled:!u.disabled})}>{u.disabled?"Mở khóa":"Khóa"}</button> <button onClick={()=>setEditing({id:u.id,sites:[...u.site_ids]})}>Sửa site</button></>}</td>
  </tr>)}
  </tbody></table>
 </section>;
}
