import pathlib,unittest
R=pathlib.Path(__file__).parents[1]
class T(unittest.TestCase):
 def test_ordered_fasttrack(self):
  s=(R/"scripts"/"AUTOCHAT_RC_RUN_ALL.bat").read_text(encoding="utf-8")
  order=["AUTOCHAT_STAGE_MATRIX","AUTOCHAT_HOCUONG_PREFLIGHT","A1_A3_AUTOMATED_QA","A4_A5_LIVE_QA","A6_RESTART_ACCEPTANCE","A7_PACKAGE_PUBLIC_RC","AUTOCHAT_PUBLIC_GATE"]
  pos=[s.index(x) for x in order];self.assertEqual(pos,sorted(pos))
if __name__=="__main__":unittest.main()
