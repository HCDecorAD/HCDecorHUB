"use client";
import {useState} from "react";
export default function Login(){
 const [err,setErr]=useState("");
 async function submit(e){
  e.preventDefault();setErr("");
  const f=new FormData(e.target);
  const r=await fetch("/api/auth/login",{method:"POST",headers:{"content-type":"application/json"},body:JSON.stringify({username:f.get("username"),password:f.get("password")})});
  const d=await r.json().catch(()=>({}));
  if(r.ok&&d.ok)location.href=d.redirect;else setErr("Sai tên đăng nhập hoặc mật khẩu.");
 }
 return <main className="admin"><h1>Đăng nhập</h1><form onSubmit={submit}><p><input name="username" placeholder="Tên đăng nhập" autoComplete="username" required/></p><p><input name="password" type="password" placeholder="Mật khẩu" autoComplete="current-password" required/></p><button className="btn primary" type="submit">Đăng nhập</button>{err&&<p role="alert">{err}</p>}</form></main>;
}
