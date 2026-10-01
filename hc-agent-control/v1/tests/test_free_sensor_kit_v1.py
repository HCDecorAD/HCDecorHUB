import pathlib,unittest
from src.core.free_sensor_kit import free_sensor_kit
class FreeKit(unittest.TestCase):
 def test_all_zero_cost(self):self.assertTrue(all(x["cost"]==0 for x in free_sensor_kit()))
 def test_ocr_last(self):self.assertEqual(free_sensor_kit()[-1]["name"],"tesseract")
 def test_inventory_has_no_action_primitives(self):
  s=(pathlib.Path(__file__).parents[1]/"src"/"core"/"free_sensor_kit.py").read_text().lower()
  for x in ("createtarget","publicentry","chatgpt.com"):self.assertNotIn(x,s)
if __name__=="__main__":unittest.main()
