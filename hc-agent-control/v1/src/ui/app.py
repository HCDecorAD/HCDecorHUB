import pathlib,tkinter as tk,webbrowser,datetime,threading
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
from src.core.live_status import LiveStatusTracker
from src.core.transitions import TransitionTracker
from src.core.win_notify import notify
from src.core.alerts import AlertTracker
from src.core.public_entry import PublicEntry
ROOT=pathlib.Path(__file__).resolve().parents[2]
class AutoChatApp(tk.Tk):
 def __init__(self):
  enable_windows_dpi();super().__init__();self.instance=SingleInstance(ROOT/"runtime"/"ui.lock")
  try:self.instance.acquire()
  except AlreadyRunning:self.destroy();raise SystemExit("HC Agent Control is already running")
  self.protocol("WM_DELETE_WINDOW",self.close_app);self.settings=Settings(ROOT/"config"/"settings.json");self.title("HC HUB · AutoChat Mission Control");self.geometry(self.settings.get("window_geometry","1220x760"));self.minsize(960,620)
  self.registry=ChatRegistry(ROOT/"data"/"chats.json");self.queue=CommandQueue(ROOT/"data"/"queue.json");self.discovery=CDPDiscovery();self.live=LiveStatusTracker();self.transitions=TransitionTracker();self.alerts=AlertTracker()
  self.status=tk.StringVar(value="SAFE · SEND OFF");self.alias=tk.StringVar(value="MASTER");self.command=tk.StringVar(value="/auto");self.theme=tk.StringVar(value=self.settings.get("theme","system"));self.pages=[];self.refresh_job=None;self.queue_filter=tk.StringVar(value="ALL");self._build();self.apply_theme();self.refresh();self.after(300,self.maybe_first_run);self.schedule_refresh()
 def _build(self):
  top=ttk.Frame(self,padding=(16,12,16,8));top.pack(fill="x")
  brand=ttk.Frame(top);brand.pack(side="left")
  ttk.Label(brand,text="iMaster",style="Brand.TLabel").pack(side="left")
  ttk.Label(brand,text="  Chat Control",style="Hero.TLabel").pack(side="left")
  ttk.Label(top,textvariable=self.status,style="Status.TLabel").pack(side="right",padx=12)

  self.tabs=ttk.Notebook(self);self.tabs.pack(fill="both",expand=True,padx=12,pady=(0,12))
  chat_tab=ttk.Frame(self.tabs,padding=12);self.tabs.add(chat_tab,text="Chat Control")
  queue_tab=ttk.Frame(self.tabs,padding=12);self.tabs.add(queue_tab,text="Queue")
  activity_tab=ttk.Frame(self.tabs,padding=12);self.tabs.add(activity_tab,text="Activity")
  settings_tab=ttk.Frame(self.tabs,padding=12);self.tabs.add(settings_tab,text="Settings")

  body=ttk.Panedwindow(chat_tab,orient="horizontal");body.pack(fill="both",expand=True)
  left=ttk.Frame(body,padding=10);right=ttk.Frame(body,padding=10);body.add(left,weight=2);body.add(right,weight=3)

  ttk.Label(left,text="CHATS OPEN",style="Section.TLabel").pack(anchor="w")
  ttk.Label(left,text="Chọn chat đang mở, gắn alias và theo dõi trạng thái.",style="Hint.TLabel").pack(anchor="w",pady=(0,8))
  self.tree=ttk.Treeview(left,columns=("alias","status","id","title"),show="headings",height=15)
  for c,t in (("alias","Alias"),("status","Status"),("id","Conversation"),("title","Title")): self.tree.heading(c,text=t)
  self.tree.pack(fill="both",expand=True,pady=8)
  bar=ttk.Frame(left);bar.pack(fill="x")
  ttk.Button(bar,text="Refresh / Watch",command=self.refresh).pack(side="left")
  ttk.Button(bar,text="Bind Alias",command=self.bind_selected).pack(side="left",padx=4)
  ttk.Button(bar,text="Open Chat",command=self.open_chat).pack(side="left",padx=4)
  ttk.Button(bar,text="Pause",command=self.toggle_pause).pack(side="left",padx=4)

  ttk.Label(right,text="SEND COMMAND",style="Section.TLabel").pack(anchor="w")
  ttk.Label(right,text="Chọn alias → nhập lệnh → gửi → verify.",style="Hint.TLabel").pack(anchor="w",pady=(0,10))
  row=ttk.Frame(right);row.pack(fill="x",pady=8)
  ttk.Combobox(row,textvariable=self.alias,values=("MASTER","GSC","VISUAL","VIDEO","WORKER-01"),width=16,state="readonly").pack(side="left")
  ttk.Entry(row,textvariable=self.command).pack(side="left",fill="x",expand=True,padx=8)
  ttk.Button(row,text="SEND",command=self.public_new,style="Accent.TButton").pack(side="right")

  quick=ttk.LabelFrame(right,text="Quick commands",padding=10);quick.pack(fill="x",pady=(10,8))
  for v in ("iMaster next","/auto","Tiếp tục","Kiểm tra tiến độ"):
   ttk.Button(quick,text=v,command=lambda x=v:self.command.set(x)).pack(side="left",padx=(0,6))

  auto=ttk.LabelFrame(right,text="Auto Control",padding=10);auto.pack(fill="x",pady=(10,8))
  ttk.Button(auto,text="Dry Run",command=self.dry_run).pack(side="left")
  ttk.Button(auto,text="Resume All",command=self.resume_all).pack(side="left",padx=6)
  ttk.Button(auto,text="STOP ALL",command=self.stop_all,style="Danger.TButton").pack(side="right")

  tools=ttk.Frame(right);tools.pack(fill="x",pady=(8,0))
  ttk.Button(tools,text="Health",command=self.health).pack(side="left")
  ttk.Button(tools,text="Copy CID",command=self.copy_cid).pack(side="left",padx=6)
  ttk.Button(tools,text="Help",command=self.show_help).pack(side="left")
  ttk.Button(tools,text="Unbind",command=self.unbind).pack(side="right")

  qh=ttk.Frame(queue_tab);qh.pack(fill="x",pady=(0,8))
  ttk.Label(qh,text="QUEUE",style="Section.TLabel").pack(side="left")
  ttk.Combobox(qh,textvariable=self.queue_filter,values=("ALL","READY","RETRY","RUNNING","PASS","FAILED","CANCELLED"),width=11,state="readonly").pack(side="right")
  ttk.Button(qh,text="Filter",command=self.refresh).pack(side="right",padx=4)
  self.qtree=ttk.Treeview(queue_tab,columns=("alias","state","text"),show="headings",height=14)
  self.qtree.heading("alias",text="Alias");self.qtree.heading("state",text="State");self.qtree.heading("text",text="Command")
  self.qtree.pack(fill="both",expand=True)
  qbar=ttk.Frame(queue_tab);qbar.pack(fill="x",pady=8)
  ttk.Button(qbar,text="Retry",command=self.retry_queue).pack(side="left")
  ttk.Button(qbar,text="Cancel",command=self.cancel_queue).pack(side="left",padx=4)
  ttk.Button(qbar,text="Clear Completed",command=self.clear_completed).pack(side="left")

  ttk.Label(activity_tab,text="ACTIVITY",style="Section.TLabel").pack(anchor="w")
  ttk.Label(activity_tab,text="Runtime log được tách riêng để giao diện chính luôn sạch.",style="Hint.TLabel").pack(anchor="w",pady=(0,8))
  self.log=tk.Text(activity_tab,height=14,state="disabled");self.log.pack(fill="both",expand=True)

  ttk.Label(settings_tab,text="SETTINGS",style="Section.TLabel").pack(anchor="w")
  ttk.Label(settings_tab,text="Giao diện chỉ giữ chức năng cần thiết; Send vẫn fail-closed.",style="Hint.TLabel").pack(anchor="w",pady=(0,16))
  th=ttk.Frame(settings_tab);th.pack(anchor="w")
  ttk.Label(th,text="Theme").pack(side="left")
  ttk.Combobox(th,textvariable=self.theme,values=("system","dark","light"),width=12,state="readonly").pack(side="left",padx=8)
  ttk.Button(th,text="Apply Theme",command=self.apply_theme).pack(side="left")

 def health(self):
  try:
   pages=self.discovery.pages();self.write_log("HEALTH OK");messagebox.showinfo("HC AutoChat Health","AutoChat READY\nCDP ONLINE\nPages: "+str(len(pages))+"\nSafety: FAIL-CLOSED")
  except Exception as e:messagebox.showerror("HC AutoChat Health","CDP OFFLINE\n"+str(e))
 def copy_cid(self):
  p=self.selected_page()
  if not p:messagebox.showwarning("Copy CID","Chọn một chat thật trước.");return
  self.clipboard_clear();self.clipboard_append(p.get("conversation_id",""));self.write_log("COPIED CID "+p.get("conversation_id",""))
 def show_help(self):
  w=tk.Toplevel(self);w.title("HC AutoChat - Hướng dẫn");w.geometry("650x520")
  f=ttk.Frame(w,padding=20);f.pack(fill="both",expand=True);ttk.Label(f,text="HC AUTOCHAT · HƯỚNG DẪN",style="Hero.TLabel").pack(anchor="w")
  g="NEW + SEND: tạo chat mới và gửi thật, có exact verify.\n\nDRY RUN: thử route/queue, không gửi.\n\nRefresh: quét chat đang mở.  Bind: gắn Alias vào chat đã chọn.\nOpen Chat: mở đúng chat đã bind.  Copy CID: sao chép identity chuẩn.\n\nPause/Resume: dừng/mở một Agent.  Resume All: mở queue.\nSTOP ALL: khóa toàn bộ queue ngay.\n\nHealth: kiểm tra CDP/runtime.\n\nLưu ý: Conversation ID là identity; tên chat chỉ để nhìn."
  t=tk.Text(f,wrap="word",relief="flat",padx=12,pady=12);t.insert("1.0",g);t.configure(state="disabled");t.pack(fill="both",expand=True,pady=12);ttk.Button(f,text="ĐÃ HIỂU",command=w.destroy,style="Accent.TButton").pack(anchor="e")
 def public_new(self):
  prompt=self.command.get().strip()
  if not prompt:messagebox.showwarning("HC AutoChat","Nhập lệnh trước.");return
  if not messagebox.askyesno("NEW + SEND","Tạo chat mới và GỬI THẬT?\n\n"+prompt):return
  self.status.set("WORKING · VERIFYING");self.write_log("PUBLIC NEW started")
  def worker():
   try:
    r=PublicEntry().new(self.alias.get(),prompt,30);self.after(0,lambda:self._public_done(r))
   except Exception as e:
    msg=str(e);self.after(0,lambda:self._public_fail(msg))
  threading.Thread(target=worker,daemon=True).start()
 def _public_done(self,r):
  self.status.set("VERIFIED · PUBLIC READY");self.write_log("EXACT-SEND PASS "+r["conversation_id"]);self.refresh();messagebox.showinfo("HC AutoChat","EXACT-SEND PASS\n\nCID: "+r["conversation_id"]+"\n\n"+str(r.get("url","")))
 def _public_fail(self,e):
  self.status.set("BLOCKED · FAIL-CLOSED");self.write_log("PUBLIC NEW BLOCKED "+e);messagebox.showerror("HC AutoChat","FAIL-CLOSED\n\n"+e)
 def schedule_refresh(self):
  if self.refresh_job:self.after_cancel(self.refresh_job)
  self.refresh_job=self.after(int(self.settings.get("auto_refresh_ms",5000)),self.auto_refresh_once)
 def auto_refresh_once(self):
  try:self.refresh()
  finally:self.schedule_refresh()
 def maybe_first_run(self):
  if not self.settings.get("first_run_done",False):FirstRunWizard(self,self.finish_first_run)
 def finish_first_run(self):self.settings.set("first_run_done",True);self.write_log("FIRST RUN completed")
 def close_app(self):
  if self.refresh_job:self.after_cancel(self.refresh_job)
  self.settings.set("window_geometry",self.geometry());self.instance.release();self.destroy()
 def write_log(self,s):self.log.configure(state="normal");self.log.insert("end",datetime.datetime.now().strftime("%H:%M:%S")+"  "+s+"\n");self.log.see("end");self.log.configure(state="disabled")
 def apply_theme(self):
  name,p=resolve_theme(self.theme.get(),system_dark=True);self.settings.set("theme",self.theme.get())
  self.configure(bg=p["bg"]);self.option_add("*TCombobox*Listbox.background",p["bg"]);self.option_add("*TCombobox*Listbox.foreground",p["fg"])
  st=ttk.Style(self);st.theme_use("clam")
  panel="#151c25" if name=="dark" else "#ffffff"
  muted="#94a3b8" if name=="dark" else "#475569"
  hover="#243041" if name=="dark" else "#e8edf3"
  brand="#e53935";accent="#e53935";danger="#b91c1c"
  st.configure(".",background=p["bg"],foreground=p["fg"],font=("Segoe UI",10))
  st.configure("TFrame",background=p["bg"]);st.configure("TLabel",background=p["bg"],foreground=p["fg"])
  st.configure("Brand.TLabel",font=("Segoe UI Semibold",22),foreground=brand)
  st.configure("Hero.TLabel",font=("Segoe UI Semibold",22),foreground=p["fg"])
  st.configure("Section.TLabel",font=("Segoe UI Semibold",11),foreground=muted)
  st.configure("Hint.TLabel",font=("Segoe UI",9),foreground=muted)
  st.configure("Status.TLabel",font=("Segoe UI Semibold",9),foreground=muted)
  st.configure("TNotebook",background=p["bg"],borderwidth=0)
  st.configure("TNotebook.Tab",padding=(16,9),background=panel,foreground=muted,borderwidth=0)
  st.map("TNotebook.Tab",background=[("selected",hover)],foreground=[("selected",p["fg"])])
  st.configure("TLabelframe",background=p["bg"],foreground=muted)
  st.configure("TLabelframe.Label",background=p["bg"],foreground=muted,font=("Segoe UI Semibold",9))
  st.configure("TButton",padding=(10,7),background=panel,foreground=p["fg"],borderwidth=0)
  st.map("TButton",background=[("active",hover)])
  st.configure("Accent.TButton",background=accent,foreground="#ffffff",font=("Segoe UI Semibold",10))
  st.map("Accent.TButton",background=[("active","#c62828")])
  st.configure("Danger.TButton",background=danger,foreground="#ffffff",font=("Segoe UI Semibold",10))
  st.configure("Treeview",background=panel,fieldbackground=panel,foreground=p["fg"],rowheight=30,borderwidth=0)
  st.configure("Treeview.Heading",background=p["bg"],foreground=muted,font=("Segoe UI Semibold",9),borderwidth=0)
  st.configure("TEntry",fieldbackground=panel,foreground=p["fg"]);st.configure("TCombobox",fieldbackground=panel,background=panel,foreground=p["fg"])
  if hasattr(self,"log"):
   self.log.configure(bg=panel,fg=p["fg"],insertbackground=p["fg"],relief="flat",padx=10,pady=8)
   self.write_log("THEME "+name)

 def refresh(self):
  self.tree.delete(*self.tree.get_children());self.qtree.delete(*self.qtree.get_children())
  items=self.queue.items if self.queue_filter.get()=="ALL" else [x for x in self.queue.items if x.get("state")==self.queue_filter.get()]
  for x in items[-100:]:self.qtree.insert("","end",iid=x.get("id"),values=(x.get("alias",""),x.get("state",""),x.get("text","")))
  paused="STOPPED" if self.queue.global_paused else "SAFE"
  try:self.pages=self.discovery.pages();live=self.live.update(self.pages);self.status.set("ONLINE · "+paused+" · SEND OFF")
  except Exception as e:self.pages=[];self.status.set("OFFLINE · SEND OFF");self.write_log("CDP ERROR "+str(e))
  bound={x["conversation_id"]:x["alias"] for x in self.registry.all()}
  current={cid:self.live.state(cid) for cid in bound}
  for e in self.transitions.update(current):
   if e["to"] in ("OFFLINE","LOST"):
    self.write_log("ALERT "+str(e))
    if self.settings.get("notifications",True):notify("HC Agent Control",e["id"]+" "+e["to"])
   elif e["from"] in ("OFFLINE","LOST") and e["to"] in ("IDLE","WAITING"):self.write_log("RECOVERED "+str(e))
  states={}
  for p in self.pages:
   cid=p.get("conversation_id","");alias=bound.get(cid,"");state=("PAUSED" if alias in self.queue.paused_aliases else self.live.state(cid)) if cid else "ROOT";self.tree.insert("","end",values=(alias,state,cid,p.get("title","")),tags=(p.get("target_id",""),))
   if alias:states[alias]=state
  for item in self.registry.all():
   if item["alias"] not in states:states[item["alias"]]="OFFLINE"
  for a in self.alerts.transitions(states):self.write_log("ALERT "+a["alias"]+" "+str(a["from"])+" -> "+a["to"])
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
  qid=s[0]
  return next((x for x in self.queue.items if x.get("id")==qid),None)
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
