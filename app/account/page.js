"use client";
import {useState} from "react";
export default function Account(){
 const [msg,setMsg]=useState("");
 async function submit(e){
  e.preventDefault();setMsg("");
  const f=new FormData(e.target);
  const r=await fetch("/api/auth/password",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({current:f.get("current"),next:f.get("next")})});
  setMsg(r.ok?"Đã đổi mật khẩu.":r.status===400?"Mật khẩu mới phải từ 10 ký tự và khác mật khẩu cũ.":"Mật khẩu hiện tại không đúng hoặc phiên hết hạn.");
  if(r.ok)e.target.reset();
 }
 return <main className="admin"><h1>Tài khoản</h1><form onSubmit={submit}><p><input name="current" type="password" placeholder="Mật khẩu hiện tại" autoComplete="current-password" required/></p><p><input name="next" type="password" placeholder="Mật khẩu mới (từ 10 ký tự)" autoComplete="new-password" minLength={10} required/></p><button className="btn primary" type="submit">Đổi mật khẩu</button>{msg&&<p role="status">{msg}</p>}</form></main>;
}
