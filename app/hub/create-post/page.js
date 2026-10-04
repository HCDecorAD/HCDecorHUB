import {HubShell} from "../../../components/HubShell";
import CreatePostClient from "./CreatePostClient";
export default function CreatePost(){
  return <HubShell title="Create Post" eyebrow="WIN_CREATE_POST / SOCIAL FACTORY">
    <div className="notice">Content → Media → Networks → Accounts → AI Variants → Preview. AI steps remain explicitly blocked until a verified provider/model binding exists.</div>
    <CreatePostClient/>
  </HubShell>
}
