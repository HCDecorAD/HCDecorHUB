import unittest
from src.adapters.uia_postverify import verify_new_exact
class PV(unittest.TestCase):
 def test_exact_growth(self):
  seq=[["aaaa","command"]]
  def walk(w):return [type("C",(),{"Name":x,"ControlTypeName":"TextControl"})() for x in seq[0]]
  import src.adapters.uia_postverify as m
  old=m.time.sleep;m.time.sleep=lambda x:None
  try:self.assertTrue(verify_new_exact(walk,None,"command",["a"],.01))
  finally:m.time.sleep=old
if __name__=="__main__":unittest.main()
