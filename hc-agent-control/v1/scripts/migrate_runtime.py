import pathlib
from src.core.migrate import migrate
root=pathlib.Path(__file__).resolve().parents[1];print("MIGRATION_PASS",migrate(root))
