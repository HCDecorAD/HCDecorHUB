import subprocess,pathlib,sys,json,time
root=pathlib.Path(__file__).resolve().parents[1];logs=root/"logs";logs.mkdir(exist_ok=True)
jobs={"unit":"RUN_STAGING_TESTS.bat","stress":"40_STRESS_TEST.bat","recovery":"41_RECOVERY_TEST.bat","ui":"31_TEST_UI.bat"}
running={}
for name,bat in jobs.items():
 f=open(logs/f"parallel-{name}.log","w",encoding="utf-8");running[name]=(subprocess.Popen(["cmd","/c",str(root/"scripts"/bat)],cwd=root,stdout=f,stderr=subprocess.STDOUT),f)
results={}
for name,(p,f) in running.items():results[name]=p.wait();f.close()
(logs/"parallel-results.json").write_text(json.dumps(results,indent=2),encoding="utf-8")
print("PARALLEL_RESULTS",results)
raise SystemExit(0 if all(v==0 for v in results.values()) else 290)
