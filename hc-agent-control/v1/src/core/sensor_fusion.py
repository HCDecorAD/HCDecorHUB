from dataclasses import dataclass
@dataclass(frozen=True)
class Sensor:
 name:str; priority:int; role:str; structured:bool=True
SENSORS=(
 Sensor("UIA_EVENT_RADAR",10,"window/tab/control discovery"),
 Sensor("CDP_DEVTOOLS",20,"exact browser identity/DOM"),
 Sensor("PLAYWRIGHT_CANARY",30,"role/text locators and actionability"),
 Sensor("WINDOW_CAPTURE",40,"visual state verification",False),
 Sensor("VISION_FALLBACK",50,"visual anomaly recognition",False),
 Sensor("OCR_FALLBACK",60,"text recovery when structure unavailable",False),
)
def sensor_plan(cdp=False,uia=True):
 out=[]
 for s in SENSORS:
  if s.name=="CDP_DEVTOOLS" and not cdp:continue
  if s.name=="UIA_EVENT_RADAR" and not uia:continue
  out.append({"name":s.name,"priority":s.priority,"role":s.role,"structured":s.structured})
 return out
