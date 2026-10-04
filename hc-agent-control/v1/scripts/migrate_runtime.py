import pathlib,sys
root=pathlib.Path(__file__).resolve().parents[1]
if str(root) not in sys.path: sys.path.insert(0,str(root))
from src.core.migrate import migrate
print("MIGRATION_PASS",migrate(root))
