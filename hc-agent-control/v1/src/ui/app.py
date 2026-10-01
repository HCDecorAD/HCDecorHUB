import pathlib,tkinter as tk
from tkinter import ttk
from src.adapters.cdp import CDPDiscovery
from src.core.registry import ChatRegistry
from src.core.queue import CommandQueue
from src.core.resolver import AliasResolver,AliasResolutionError
ROOT=pathlib.Path(__file__).resolve().parents[2]
class AutoChatApp(tk.Tk):
 def __init__(self):
  super().__init__();self.title("HC Agent Control — AutoChat V1");self.geometry("1180x720");self.minsize(920,600)
  self.registry=ChatRegistry(ROOT/"data"/"chats.json");self.queue=CommandQueue(ROOT/"data"/"queue.json");self.discovery=CDPDiscovery();self.status=tk.StringVar(value="SAFE · SEND OFF");self.alias=tk.StringVar(value="MASTER");self.command=tk.StringVar(value="/auto");self._build();self.refresh()
 def _build(self):
  top=ttk.Frame(self,padding=12);top.pack(fill="x");ttk.Label(top,text="HC Agent Control",font=("Segoe UI",18,"bold")).pack(side="left");ttk.Label(top,textvariable=self.status).pack(side="right")
  body=ttk.Panedwindow(self,orient="horizontal");body.pack(fill="both",expand=True,padx=12,pady=(0,12));left=ttk.Frame(body,padding=10);right=ttk.Frame(body,padding=10);body.add(left,weight=2);body.add(right,weight=3)
  ttk.Label(left,text="Chat Windows",font=("Segoe UI",12,"bold")).pack(anchor="w");self.tree=ttk.Treeview(left,columns=("alias","status","id"),show="headings",height=14)
  for c,t in (("alias","Alias"),("status","Status"),("id","Conversation")):self.tree.heading(c,text=t)
  self.tree.pack(fill="both",expand=True,pady=8);ttk.Button(left,text="Refresh",command=self.refresh).pack(side="left");ttk.Button(left,text="STOP ALL",command=self.stop_all).pack(side="right")
  ttk.Label(right,text="Command",font=("Segoe UI",12,"bold")).pack(anchor="w");row=ttk.Frame(right);row.pack(fill="x",pady=8);ttk.Combobox(row,textvariable=self.alias,values=("MASTER","GSC","VISUAL","VIDEO","WORKER-01"),width=16,state="readonly").pack(side="left");ttk.Entry(row,textvariable=self.command).pack(side="left",fill="x",expand=True,padx=8);ttk.Button(row,text="Dry Run",command=self.dry_run).pack(side="right")
  quick=ttk.Frame(right);quick.pack(fill="x")
  for v in ("/auto","Tiếp tục","Kiểm tra tiến độ"):ttk.Button(quick,text=v,command=lambda x=v:self.command.set(x)).pack(side="left",padx=(0,6))
  ttk.Label(right,text="Live Log",font=("Segoe UI",12,"bold")).pack(anchor="w",pady=(18,6));self.log=tk.Text(right,height=22,state="disabled");self.log.pack(fill="both",expand=True)
 def write_log(self,s):self.log.configure(state="normal");self.log.insert("end",s+"\n");self.log.see("end");self.log.configure(state="disabled")
 def refresh(self):
  self.tree.delete(*self.tree.get_children())
  try:pages=self.discovery.pages();self.status.set("ONLINE · SAFE · SEND OFF")
  except Exception as e:pages=[];self.status.set("OFFLINE · SEND OFF");self.write_log("CDP ERROR "+str(e))
  for saved in self.registry.all():
   try:cur=AliasResolver.resolve(saved,pages);state="ONLINE";cid=cur["conversation_id"]
   except AliasResolutionError:state="OFFLINE";cid=saved.get("conversation_id","")
   self.tree.insert("","end",values=(saved["alias"],state,cid))
 def stop_all(self):self.queue.stop_all();self.status.set("STOPPED · SEND OFF");self.write_log("STOP ALL")
 def dry_run(self):
  if self.queue.global_paused:self.write_log("BLOCKED global pause");return
  item=self.queue.enqueue(self.alias.get(),self.command.get());self.write_log(f"DRY RUN {item['alias']} {item['text']} [{item['id']}]")
if __name__=="__main__":AutoChatApp().mainloop()
