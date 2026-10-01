import tkinter as tk
from tkinter import ttk

class AutoChatApp(tk.Tk):
    def __init__(self):
        super().__init__();self.title("HC Agent Control — AutoChat V1");self.geometry("1180x720");self.minsize(920,600)
        self.status=tk.StringVar(value="SAFE · SEND OFF");self.alias=tk.StringVar(value="MASTER");self.command=tk.StringVar(value="/auto")
        self._build()
    def _build(self):
        top=ttk.Frame(self,padding=12);top.pack(fill="x");ttk.Label(top,text="HC Agent Control",font=("Segoe UI",18,"bold")).pack(side="left");ttk.Label(top,textvariable=self.status).pack(side="right")
        body=ttk.Panedwindow(self,orient="horizontal");body.pack(fill="both",expand=True,padx=12,pady=(0,12))
        left=ttk.Frame(body,padding=10);right=ttk.Frame(body,padding=10);body.add(left,weight=2);body.add(right,weight=3)
        ttk.Label(left,text="Chat Windows",font=("Segoe UI",12,"bold")).pack(anchor="w");self.tree=ttk.Treeview(left,columns=("status","id"),show="headings",height=14);self.tree.heading("status",text="Status");self.tree.heading("id",text="Conversation");self.tree.pack(fill="both",expand=True,pady=8)
        ttk.Button(left,text="Refresh").pack(side="left");ttk.Button(left,text="STOP ALL",command=lambda:self.status.set("STOPPED · SEND OFF")).pack(side="right")
        ttk.Label(right,text="Command",font=("Segoe UI",12,"bold")).pack(anchor="w");row=ttk.Frame(right);row.pack(fill="x",pady=8);ttk.Combobox(row,textvariable=self.alias,values=("MASTER","GSC","VISUAL","VIDEO","WORKER-01"),width=16,state="readonly").pack(side="left");ttk.Entry(row,textvariable=self.command).pack(side="left",fill="x",expand=True,padx=8);ttk.Button(row,text="Dry Run",command=self.dry_run).pack(side="right")
        quick=ttk.Frame(right);quick.pack(fill="x");
        for text in ("/auto","Tiếp tục","Kiểm tra tiến độ"):ttk.Button(quick,text=text,command=lambda v=text:self.command.set(v)).pack(side="left",padx=(0,6))
        ttk.Label(right,text="Live Log",font=("Segoe UI",12,"bold")).pack(anchor="w",pady=(18,6));self.log=tk.Text(right,height=22,state="disabled");self.log.pack(fill="both",expand=True)
    def dry_run(self):
        self.log.configure(state="normal");self.log.insert("end",f"DRY RUN  {self.alias.get()}  {self.command.get()}\n");self.log.see("end");self.log.configure(state="disabled")
if __name__=="__main__":AutoChatApp().mainloop()
