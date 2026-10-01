import tkinter as tk
from tkinter import ttk
class FirstRunWizard(tk.Toplevel):
 def __init__(self,parent,on_done):
  super().__init__(parent);self.title("HC Agent Control — First Run");self.geometry("620x390");self.transient(parent);self.grab_set()
  box=ttk.Frame(self,padding=24);box.pack(fill="both",expand=True)
  ttk.Label(box,text="HC Agent Control — Setup",font=("Segoe UI",18,"bold")).pack(anchor="w")
  ttk.Label(box,text="1. Managed Edge must be running on local CDP 9222.\n2. Sign in to ChatGPT in that managed Edge profile.\n3. Open at least two real /c/ conversations.\n4. Bind each conversation to an Alias.\n5. Keep STOP ALL enabled while reviewing mappings.\n\nReal Send remains OFF in this build.",justify="left").pack(anchor="w",pady=20)
  ttk.Button(box,text="I understand — Open Dashboard",command=lambda:(on_done(),self.destroy())).pack(anchor="e")
