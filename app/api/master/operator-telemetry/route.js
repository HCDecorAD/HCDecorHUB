import {operatorTelemetrySnapshot} from "../../../../lib/operator-telemetry";
export async function GET(){return Response.json(await operatorTelemetrySnapshot())}
