import pathlib,tkinter as tk,webbrowser,datetime
from tkinter import ttk,messagebox
from src.adapters.cdp import CDPDiscovery
from src.core.registry import ChatRegistry,RegistryConflict
from src.core.queue import CommandQueue
from src.core.resolver import AliasResolver,AliasResolutionError
from src.core.theme import resolve_theme
from src.core.single_instance import SingleInstance,AlreadyRunning
from src.core.settings import Settings
from src.core.dpi import enable_windows_dpi
from src.ui.first_run import FirstRunWizard
ROOT=pathlib.Path(__file__).resolve().parents[2]
class AutoChatApp(tk.Tk):
 def __init__(self):
  enable_windows_dpi();super().__init__();self.instance=SingleInstance(ROOT/"runtime"/"ui.lock")
  try:self.instance.acquire()
  except AlreadyRunning:self.destroy();raise SystemExit("HC Agent Control is already running")
  self.protocol("WM_DELETE_WINDOW",self.close_app);self.settings=Settings(ROOT/"config"/"settings.json");self.title("HC Agent Control — AutoChat V1");self.geometry(self.settings.get("window_geometry","1220x760"));self.minsize(960,620)
  self.registry=ChatRegistry(ROOT/"data"/"chats.json");self.queue=CommandQueue(ROOT/"data"/"queue.json");self.discovery=CDPDiscovery()
  self.status=tk.StringVar(value="SAFE · SEND OFF");self.alias=tk.StringVar(value="MASTER");self.command=tk.StringVar(value="/auto");self.theme=tk.StringVar(value=self.settings.get("theme","system"));self.pages=[];self.queue_filter=tk.StringVar(value="ALL");self._build();self.apply_theme();self.refresh();self.after(300,self.maybe_first_run)
 def _build(self):
  top=ttk.Frame(self,padding=12);top.pack(fill="x");ttk.Label(top,text="HC Agent Control",font=("Segoe UI",18,"bold")).pack(side="left")
  ttk.Combobox(top,textvariable=self.theme,values=("system","dark","light"),width=9,state="readonly").pack(side="right",padx=8);ttk.Button(top,text="Theme",command=self.apply_theme).pack(side="right");ttk.Label(top,textvariable=self.status).pack(side="right",padx=12)
  body=ttk.Panedwindow(self,orient="horizontal");body.pack(fill="both",expand=True,padx=12,pady=(0,12));left=ttk.Frame(body,padding=10);right=ttk.Frame(body,padding=10);body.add(left,weight=2);body.add(right,weight=3)
  ttk.Label(left,text="Chat Windows",font=("Segoe UI",12,"bold")).pack(anchor="w");self.tree=ttk.Treeview(left,columns=("alias","status","id","title"),show="headings",height=15)
  for c,t in (("alias","Alias"),("status","Status"),("id","Conversation"),("title","Title")):self.tree.heading(c,text=t)
  self.tree.pack(fill="both",expand=True,pady=8)
  bar=ttk.Frame(left);bar.pack(fill="x");ttk.Button(bar,text="Refresh",command=self.refresh).pack(side="left");ttk.Button(bar,text="Bind",command=self.bind_selected).pack(side="left",padx=4);ttk.Button(bar,text="Unbind",command=self.unbind).pack(side="left");ttk.Button(bar,text="Open Chat",command=self.open_chat).pack(side="left",padx=4);ttk.Button(bar,text="Pause/Resume",command=self.toggle_pause).pack(side="left",padx=4);ttk.Button(bar,text="Resume All",command=self.resume_all).pack(side="right",padx=4);ttk.Button(bar,text="STOP ALL",command=self.stop_all).pack(side="right")
  ttk.Label(right,text="Command",font=("Segoe UI",12,"bold")).pack(anchor="w");row=ttk.Frame(right);row.pack(fill="x",pady=8);ttk.Combobox(row,textvariable=self.alias,values=("MASTER","GSC","VISUAL","VIDEO","WORKER-01"),width=16,state="readonly").pack(side="left");ttk.Entry(row,textvariable=self.command).pack(side="left",fill="x",expand=True,padx=8);ttk.Button(row,text="Dry Run",command=self.dry_run).pack(side="right")
  quick=ttk.Frame(right);quick.pack(fill="x")
  for v in ("/auto","Tiếp tục","Kiểm tra tiến độ"):ttk.Button(quick,text=v,command=lambda x=v:self.command.set(x)).pack(side="left",padx=(0,6))
  qh=ttk.Frame(right);qh.pack(fill="x",pady=(14,4));ttk.Label(qh,text="Queue",font=("Segoe UI",12,"bold")).pack(side="left");ttk.Combobox(qh,textvariable=self.queue_filter,values=("ALL","READY","RETRY","RUNNING","PASS","FAILED","CANCELLED"),width=11,state="readonly").pack(side="right");ttk.Button(qh,text="Filter",command=self.refresh).pack(side="right",padx=4);self.qtree=ttk.Treeview(right,columns=("alias","state","text"),show="headings",height=6);self.qtree.heading("alias",text="Alias");self.qtree.heading("state",text="State");self.qtree.heading("text",text="Command");self.qtree.pack(fill="x");qbar=ttk.Frame(right);qbar.pack(fill="x",pady=(4,0));ttk.Button(qbar,text="Retry",command=self.retry_queue).pack(side="left");ttk.Button(qbar,text="Cancel",command=self.cancel_queue).pack(side="left",padx=4);ttk.Button(qbar,text="Clear Completed",command=self.clear_completed).pack(side="left")
  ttk.Label(right,text="Live Log",font=("Segoe UI",12,"bold")).pack(anchor="w",pady=(12,6));self.log=tk.Text(right,height=14,state="disabled");self.log.pack(fill="both",expand=True)
 def maybe_first_run(self):
  if not self.settings.get("first_run_done",False):FirstRunWizard(self,self.finish_first_run)
 def finish_first_run(self):self.settings.set("first_run_done",True);self.write_log("FIRST RUN completed")
 def close_app(self):self.settings.set("window_geometry",self.geometry());self.instance.release();self.destroy()
 def write_log(self,s):self.log.configure(state="normal");self.log.insert("end",datetime.datetime.now().strftime("%H:%M:%S")+"  "+s+"\n");self.log.see("end");self.log.configure(state="disabled")
 def apply_theme(self):
  name,p=resolve_theme(self.theme.get(),system_dark=True);self.settings.set("theme",self.theme.get());self.configure(bg=p["bg"]);self.option_add("*TCombobox*Listbox.background",p["bg"]);self.option_add("*TCombobox*Listbox.foreground",p["fg"]);self.write_log("THEME "+name) if hasattr(self,"log") else None
 def refresh(self):
  self.tree.delete(*self.tree.get_children());self.qtree.delete(*self.qtree.get_children())
  items=self.queue.items if self.queue_filter.get()=="ALL" else [x for x in self.queue.items if x.get("state")==self.queue_filter.get()]
  for x in items[-100:]:self.qtree.insert("","end",values=(x.get("alias",""),x.get("state",""),x.get("text","")))
  paused="STOPPED" if self.queue.global_paused else "SAFE"
  try:self.pages=self.discovery.pages();self.status.set("ONLINE · "+paused+" · SEND OFF")
  except Exception as e:self.pages=[];self.status.set("OFFLINE · SEND OFF");self.write_log("CDP ERROR "+str(e))
  bound={x["conversation_id"]:x["alias"] for x in self.registry.all()}
  for p in self.pages:
   cid=p.get("conversation_id","");alias=bound.get(cid,"");state=("PAUSED" if alias in self.queue.paused_aliases else "ONLINE") if cid else "ROOT";self.tree.insert("","end",values=(alias,state,cid,p.get("title","")),tags=(p.get("target_id",""),))
 def selected_page(self):
  s=self.tree.selection()
  if not s:return None
  vals=self.tree.item(s[0],"values");cid=vals[2]
  return next((p for p in self.pages if p.get("conversation_id")==cid and cid),None)
 def bind_selected(self):
  p=self.selected_page()
  if not p:messagebox.showwarning("HC Agent Control","Select a real /c/ conversation first.");return
  try:self.registry.bind(self.alias.get(),p);self.write_log("BOUND "+self.alias.get()+" -> "+p["conversation_id"]);self.refresh()
  except RegistryConflict as e:messagebox.showerror("Bind blocked",str(e))
 def open_chat(self):
  saved=self.registry.get(self.alias.get())
  if saved and saved.get("url"):webbrowser.open(saved["url"]);self.write_log("OPEN "+self.alias.get())
  else:self.write_log("OPEN BLOCKED alias not bound "+self.alias.get())
 def unbind(self):self.registry.unbind(self.alias.get());self.write_log("UNBOUND "+self.alias.get());self.refresh()
 def toggle_pause(self):
  a=self.alias.get()
  if a in self.queue.paused_aliases:self.queue.resume(a);self.write_log("RESUME "+a)
  else:self.queue.pause(a);self.write_log("PAUSE "+a)
  self.refresh()
 def selected_queue_item(self):
  s=self.qtree.selection()
  if not s:return None
  idx=self.qtree.index(s[0]);items=self.queue.items[-100:]
  return items[idx] if idx<len(items) else None
 def retry_queue(self):
  x=self.selected_queue_item()
  if not x:self.write_log("RETRY BLOCKED no queue selection");return
  try:self.queue.retry(x["id"]);self.write_log("RETRY "+x["id"]);self.refresh()
  except Exception as e:self.write_log("RETRY BLOCKED "+str(e))
 def cancel_queue(self):
  x=self.selected_queue_item()
  if not x:self.write_log("CANCEL BLOCKED no queue selection");return
  try:self.queue.cancel(x["id"]);self.write_log("CANCEL "+x["id"]);self.refresh()
  except Exception as e:self.write_log("CANCEL BLOCKED "+str(e))
 def clear_completed(self):
  keep={"READY","RUNNING","RETRY"}
  self.queue.items=[x for x in self.queue.items if x.get("state") in keep];self.queue.keys={x.get("idempotency_key") for x in self.queue.items if x.get("idempotency_key")};self.queue._save();self.write_log("QUEUE cleared completed");self.refresh()
 def resume_all(self):self.queue.resume();self.write_log("RESUME ALL");self.refresh()
 def stop_all(self):self.queue.stop_all();self.status.set("STOPPED · SEND OFF");self.write_log("STOP ALL")
 def dry_run(self):
  a=self.alias.get()
  if self.queue.global_paused or a in self.queue.paused_aliases:self.write_log("BLOCKED paused "+a);return
  saved=self.registry.get(a)
  if not saved:self.write_log("BLOCKED alias not bound "+a);return
  try:AliasResolver.resolve(saved,self.pages)
  except AliasResolutionError as e:self.write_log("BLOCKED identity "+str(e));return
  item=self.queue.enqueue(a,self.command.get());self.write_log(f"DRY RUN {item['alias']} {item['text']} [{item['id']}]");self.refresh()
if __name__=="__main__":AutoChatApp().mainloop()
