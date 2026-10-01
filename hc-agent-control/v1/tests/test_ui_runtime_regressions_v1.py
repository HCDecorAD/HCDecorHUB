import pathlib,unittest
class T(unittest.TestCase):
 def setUp(self):self.s=(pathlib.Path(__file__).parents[1]/"src"/"ui"/"app.py").read_text(encoding="utf-8")
 def test_refresh_job_initialized(self):self.assertIn("self.refresh_job=None",self.s)
 def test_no_bool_method_collision(self):self.assertNotIn("self.auto_refresh=True",self.s);self.assertIn("def auto_refresh_once",self.s)
 def test_queue_uses_id(self):self.assertIn("iid=x.get(\"id\")",self.s);self.assertIn("qid=s[0]",self.s)
if __name__=="__main__":unittest.main()
