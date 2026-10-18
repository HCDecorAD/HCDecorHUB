"use client";
import {useEffect,useRef,useState} from "react";
// Tab "Quản lý" trên SuperAdmin: tạo ID/Pass, chọn ID, đặt lại Pass (mở box ngay trong trang), khóa, sửa site.
// API tự kiểm tra owner. Mật khẩu chỉ giữ trong state của component, hiện một lần.
const SITES=["amo","gsc"],MIN=8;
const ROLE_LABEL={staff:"NV",manager:"Quản lý",guest:"Khách"};
export default function UserManager(){
 const [users,setUsers]=useState(null),[once,setOnce]=useState(null),[err,setErr]=useState("");
 const [form,setForm]=useState({username:"",role:"staff",sites:["amo"]});
 const [selected,setSelected]=useState(null);
 const [editing,setEditing]=useState(null);
 const [resetBox,setResetBox]=useState(null); // {id, pass}
 const resetRef=useRef(null);
 async function load(){
  const r=await fetch("/api/admin/users");
  if(!r.ok){setUsers([]);setErr("Chỉ SuperAdmin được truy cập.");return}
  setUsers((await r.json()).users);
 }
 async function call(payload){
  setErr("");
  const r=await fetch("/api/admin/users",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify(payload)});
  const d=await r.json().catch(()=>({}));
  if(!r.ok){setErr("Không thực hiện được: "+(d.error==="weak_password"?"Pass tối thiểu "+MIN+" ký tự":d.error||r.status));return false}
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
  if(await call({action:"update",username:u.id,site_ids:editing.sites,role:editing.role}))setEditing(null);
 }
 function openReset(u){setSelected(u.id);setResetBox({id:u.id,pass:"",t:Date.now()})}
 // Bấm Đặt lại Pass thì cuộn tới box, không để anh tự kéo tìm
 useEffect(()=>{if(resetBox)resetRef.current?.scrollIntoView({behavior:"smooth",block:"center"})},[resetBox?.t]);
 async function resetWith(random){
  const id=resetBox.id;
  const ok=await call(random?{action:"reset",username:id}:{action:"reset",username:id,password:resetBox.pass});
  if(ok)setResetBox(null);
 }
 // Xóa hẳn không khôi phục được: chỉ cho ID đã khóa, phải gõ đúng tên để xác nhận.
 async function removeUser(u){
  const typed=window.prompt("Xóa hẳn ID "+u.id+". Không khôi phục được.\nGõ đúng tên ID để xác nhận:");
  if(typed===null)return;
  if(typed.trim()!==u.id){setErr("Tên xác nhận không khớp, chưa xóa.");return}
  if(selected===u.id)setSelected(null);
  await call({action:"delete",username:u.id});
 }
 const sel=users?.find(u=>u.id===selected);
 return <section className="userManager">
  {err&&<p role="alert">{err}</p>}
  {once&&<p role="status">Mật khẩu của <b>{once.id}</b> (chỉ hiện một lần): <code>{once.password}</code> <button onClick={()=>setOnce(null)}>Ẩn</button></p>}
  <h2>Tạo ID / Pass</h2>
  <form onSubmit={create} className="userForm">
   <input name="username" placeholder="Tên đăng nhập" required value={form.username} onChange={e=>setForm({...form,username:e.target.value})}/>
   <select name="role" value={form.role} onChange={e=>setForm({...form,role:e.target.value})}><option value="staff">NV</option><option value="manager">Quản lý</option><option value="guest">Khách</option></select>
   {SITES.map(sid=><label key={sid}><input type="checkbox" checked={form.sites.includes(sid)} onChange={()=>setForm({...form,sites:toggleSite(form.sites,sid)})}/> {sid.toUpperCase()}</label>)}
   <button className="btn primary" type="submit">Tạo</button>
  </form>

  <h2>Danh sách tài khoản</h2>
  <p className="notice">Bấm vào một dòng để chọn ID. {sel?<>Đang chọn: <b>{sel.id}</b>.</>:"Chưa chọn ID nào."}</p>
  <table><thead><tr><th>ID</th><th>Vai trò</th><th>Site</th><th>Trạng thái</th><th/></tr></thead><tbody>
  {(users||[]).map(u=><tr key={u.id} onClick={()=>setSelected(u.id)} aria-selected={selected===u.id} style={{cursor:"pointer",background:selected===u.id?"rgba(96,165,250,.18)":undefined}}>
   <td>{u.id}</td><td>{editing?.id===u.id?<select value={editing.role} onChange={e=>setEditing({...editing,role:e.target.value})}><option value="staff">NV</option><option value="manager">Quản lý</option><option value="guest">Khách</option></select>:ROLE_LABEL[u.role]||u.role}</td>
   <td>{editing?.id===u.id?SITES.map(sid=><label key={sid}><input type="checkbox" checked={editing.sites.includes(sid)} onChange={()=>setEditing({...editing,sites:toggleSite(editing.sites,sid)})}/> {sid.toUpperCase()} </label>):u.site_ids.join(", ")}</td>
   <td>{u.disabled?"Khóa":u.must_change_password?"Chờ đổi Pass":"Hoạt động"}</td>
   <td onClick={e=>e.stopPropagation()}>{editing?.id===u.id?<><button onClick={()=>saveSites(u)}>Lưu site</button> <button onClick={()=>setEditing(null)}>Hủy</button></>:
    <><button onClick={()=>openReset(u)}>Đặt lại Pass</button> <button onClick={()=>call({action:"update",username:u.id,disabled:!u.disabled})}>{u.disabled?"Mở khóa":"Khóa"}</button> <button onClick={()=>setEditing({id:u.id,sites:[...u.site_ids],role:u.role})}>Sửa site</button>{u.disabled&&<> <button onClick={()=>removeUser(u)} style={{color:"#f87171"}}>Xóa hẳn</button></>}</>}</td>
  </tr>)}
  </tbody></table>

  {resetBox&&<div ref={resetRef} className="resetBox" role="dialog" aria-label="Đặt lại Pass" style={{marginTop:16,padding:16,border:"1px solid #334155",borderRadius:12}}>
   <h3>Đặt lại Pass cho {resetBox.id}</h3>
   <p className="notice">Nhập Pass mới (tối thiểu {MIN} ký tự), hoặc để hệ thống tạo ngẫu nhiên. Người này sẽ phải đổi Pass ở lần đăng nhập kế tiếp.</p>
   <input type="text" autoComplete="off" placeholder={"Pass mới (tối thiểu "+MIN+" ký tự)"} value={resetBox.pass} onChange={e=>setResetBox({...resetBox,pass:e.target.value})} style={{minWidth:280}}/>
   {" "}<button className="btn primary" disabled={resetBox.pass.length<MIN} onClick={()=>resetWith(false)}>Lưu Pass</button>
   {" "}<button onClick={()=>resetWith(true)}>Tạo ngẫu nhiên</button>
   {" "}<button onClick={()=>setResetBox(null)}>Hủy</button>
  </div>}
 </section>;
}
