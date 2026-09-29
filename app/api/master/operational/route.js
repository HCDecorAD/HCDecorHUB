import {operationalContract} from "../../../../lib/operational-contract";
export async function GET(){return Response.json(operationalContract())}
