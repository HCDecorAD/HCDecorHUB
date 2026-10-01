import pathlib,sys,unittest
R=pathlib.Path(__file__).parents[1];sys.path.insert(0,str(R))
from src.core.public_entry import PublicEntry,PublicEntryError
class D:
 base="http://127.0.0.1:9222";timeout=1
 def pages(self):return []
class T(unittest.TestCase):
 def test_prompt_required(self):
  x=PublicEntry(D())
  with self.assertRaises(PublicEntryError):x.new("x","")
 def test_cli_contract(self):
  s=(R/"scripts"/"autochat.py").read_text(encoding="utf-8")
  for x in ['add_parser("new")','--title','--prompt','conversation_id']:self.assertIn(x,s if x!='conversation_id' else (R/"src"/"core"/"public_entry.py").read_text(encoding="utf-8"))
 def test_new_chat_exact_contract(self):
  s=(R/"src"/"core"/"public_entry.py").read_text(encoding="utf-8")
  for x in ["Target.createTarget","composer_text","inp.submit()","conversation_id","last_user_text"]:self.assertIn(x,s)
if __name__=="__main__":unittest.main()
