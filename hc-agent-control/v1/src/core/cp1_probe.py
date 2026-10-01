import json,pathlib,sys
ROOT=pathlib.Path(__file__).resolve().parents[2]
sys.path.insert(0,str(ROOT))
from src.adapters.cdp import CDPDiscovery
out=ROOT/'logs'/'cp1-real-cdp.json'
try:
    pages=CDPDiscovery().pages()
except Exception as e:
    print('REAL_CDP_FAIL',repr(e));raise SystemExit(2)
out.parent.mkdir(parents=True,exist_ok=True)
out.write_text(json.dumps(pages,ensure_ascii=False,indent=2),encoding='utf-8')
conversations=[p for p in pages if p.get('conversation_id')]
print('REAL_CDP_CHAT_PAGES',len(pages))
print('REAL_CDP_CONVERSATIONS',len(conversations))
for p in pages: print(p.get('conversation_id') or 'ROOT',p.get('title',''))
raise SystemExit(0 if pages else 3)
