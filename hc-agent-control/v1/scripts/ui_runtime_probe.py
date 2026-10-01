import ast,pathlib,sys
root=pathlib.Path(__file__).resolve().parents[1];files=list((root/"src"/"ui").glob("*.py"))
for p in files:ast.parse(p.read_text(encoding="utf-8"),filename=str(p))
sys.path.insert(0,str(root))
import src.ui.app,src.ui.first_run
print("UI_RUNTIME_PROBE_PASS",len(files))
