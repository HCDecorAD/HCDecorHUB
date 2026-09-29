import {readWordPressInventory} from "../../../../lib/cms/wordpress";
export async function GET(){const x=await readWordPressInventory();return Response.json(x,{status:x.ok?200:502})}
