"use client";
import {useEffect,useState} from "react";
const KEY="hcdecor-theme";
export default function ThemeToggle(){
 const [mode,setMode]=useState("system");
 const apply=(m)=>{
  const dark=m==="dark"||(m==="system"&&window.matchMedia("(prefers-color-scheme: dark)").matches);
  document.documentElement.dataset.theme=dark?"dark":"light";
  document.documentElement.dataset.themeMode=m;
 };
 useEffect(()=>{
  const saved=localStorage.getItem(KEY)||"system"; setMode(saved); apply(saved);
  const mq=window.matchMedia("(prefers-color-scheme: dark)");
  const onChange=()=>{if((localStorage.getItem(KEY)||"system")==="system")apply("system")};
  mq.addEventListener?.("change",onChange); return()=>mq.removeEventListener?.("change",onChange);
 },[]);
 const next=()=>{const n=mode==="light"?"dark":mode==="dark"?"system":"light";localStorage.setItem(KEY,n);setMode(n);apply(n)};
 const label=mode==="light"?"☀ Sáng":mode==="dark"?"☾ Tối":"◐ Hệ thống";
 return <button className="themeToggle" onClick={next} title="Đổi giao diện Sáng / Tối / Theo hệ thống" aria-label="Đổi giao diện">{label}</button>
}