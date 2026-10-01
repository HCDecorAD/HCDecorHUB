import argparse,json,pathlib,sys
ROOT=pathlib.Path(__file__).resolve().parents[1];sys.path.insert(0,str(ROOT))
from src.core.public_entry import PublicEntry
def main(argv=None):
 p=argparse.ArgumentParser(prog="autochat");sub=p.add_subparsers(dest="command",required=True)
 n=sub.add_parser("new");n.add_argument("--title",default="");n.add_argument("--prompt",required=True);n.add_argument("--timeout",type=int,default=25)
 a=p.parse_args(argv)
 if a.command=="new":
  result=PublicEntry().new(a.title,a.prompt,a.timeout);print(json.dumps(result,ensure_ascii=False));return 0
if __name__=="__main__":raise SystemExit(main())
