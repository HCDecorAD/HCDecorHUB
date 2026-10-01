import importlib.util,json
mods={x:bool(importlib.util.find_spec(x)) for x in ("websocket","websockets")}
print(json.dumps(mods))
raise SystemExit(0 if any(mods.values()) else 360)
