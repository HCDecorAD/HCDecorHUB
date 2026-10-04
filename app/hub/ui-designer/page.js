import {HubShell} from "../../../components/HubShell";
import UIDesignerClient from "./UIDesignerClient";
export default function UIDesigner(){
 return <HubShell title="UI Designer" eyebrow="WIN_UI_DESIGNER / SCHEMA WORKSPACE">
  <div className="notice">Request → Schema → Validate → Preview → Apply → Version → Undo. AI patch stays blocked until PKG-05 is DONE.</div>
  <UIDesignerClient/>
 </HubShell>
}
