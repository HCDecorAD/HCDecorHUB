import {smokeWorkspaces} from "../../../../lib/workspace-smoke";export async function GET(){return Response.json(await smokeWorkspaces())}
