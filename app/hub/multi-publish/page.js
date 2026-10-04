import {HubShell} from "../../../components/HubShell";
import MultiPublishClient from "./MultiPublishClient";
export default function MultiPublish(){return <HubShell title="Multi Publish" eyebrow="WIN_MULTI_PUBLISH / SAFE BATCH PLANNER"><div className="notice">Post × account × schedule planning only. Every job is READY_FOR_REVIEW, idempotent and publish=false until explicit approval and a verified external execution path exist.</div><MultiPublishClient/></HubShell>}
