import pathlib,unittest
from src.core.sensor_fusion import sensor_plan
class SensorFusionPolicy(unittest.TestCase):
 def test_structured_sensors_precede_vision_ocr(self):
  p=sensor_plan(cdp=True); names=[x["name"] for x in p]
  self.assertLess(names.index("UIA_EVENT_RADAR"),names.index("VISION_FALLBACK"))
  self.assertLess(names.index("CDP_DEVTOOLS"),names.index("OCR_FALLBACK"))
 def test_no_action_or_new_chat_primitive(self):
  s=(pathlib.Path(__file__).parents[1]/"src"/"core"/"sensor_fusion.py").read_text().lower()
  for x in ("createtarget","publicentry","sendinput","chatgpt.com"):self.assertNotIn(x,s)
if __name__=="__main__":unittest.main()
