import pathlib,unittest
class T(unittest.TestCase):
 def test_daily_controls_present(self):
  s=(pathlib.Path(__file__).parents[1]/"src"/"ui"/"app.py").read_text(encoding="utf-8")
  for x in ("STOP ALL","Resume All","Retry","Cancel","Clear Completed","Open Chat","SEND OFF"):self.assertIn(x,s)
if __name__=="__main__":unittest.main()
