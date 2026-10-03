import {mkdir,access} from "node:fs/promises";
import {join} from "node:path";
export async function bootstrap({root,minimumNodeMajor=22}){
  const major=Number(process.versions.node.split(".")[0]);
  if(major<minimumNodeMajor) return {ok:false,reason:"NODE_VERSION",major};
  const dirs=["runtime-state","evidence/live","data/live","logs"];
  for(const d of dirs) await mkdir(join(root,d),{recursive:true});
  for(const d of dirs) await access(join(root,d));
  return {ok:true,major,dirs};
}
