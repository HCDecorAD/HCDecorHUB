import {execFile} from "node:child_process";import {promisify} from "node:util";import {resolve} from "node:path";import {mkdir} from "node:fs/promises";
const exec=promisify(execFile);
const parse=s=>{const t=String(s??"").trim();return t?JSON.parse(t):null;};
export function createWindowProvider({root}){
  const ps=process.env.SystemRoot?resolve(process.env.SystemRoot,"System32","WindowsPowerShell","v1.0","powershell.exe"):"powershell.exe";
  return {
    async list(){const {stdout}=await exec(ps,["-NoProfile","-ExecutionPolicy","Bypass","-File",resolve(root,"runtime/list-windows.ps1")],{windowsHide:true});const x=parse(stdout);return Array.isArray(x)?x:(x?[x]:[]);},
    async capture(win){const dir=resolve(root,"evidence/window-watch");await mkdir(dir,{recursive:true});const out=resolve(dir,`${win.processName}-${win.id}-latest.png`);const {stdout}=await exec(ps,["-NoProfile","-ExecutionPolicy","Bypass","-File",resolve(root,"runtime/capture-window.ps1"),"-Hwnd",String(win.hwnd),"-Output",out],{windowsHide:true});return parse(stdout);}
  };
}
export function createChatBridge({root}){
  const py=process.env.HC_SENTINEL_PYTHON??"python";
  const script=resolve(root,"runtime/autochat-bridge.py");
  async function call(args){try{const {stdout}=await exec(py,[script,...args],{windowsHide:true,maxBuffer:1024*1024,env:{...process.env,PYTHONIOENCODING:"utf-8",PYTHONUTF8:"1"}});return parse(stdout);}catch(e){const out=parse(e.stdout);return out??{status:"BLOCKED",error:String(e.message||e)};}}
  return {list:()=>call(["list"]),snapshot:a=>call(["snapshot",a]),send:(a,c)=>call(["send",a,c])};
}
