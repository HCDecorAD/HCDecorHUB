# Architecture

ChatGPT / iMaster
|-- DIRECT -> HC Mobile Agent -> Samsung (Gallery / Files / Apps / Camera)
|-- PC -> HCDR Local -> HOCUONG (Windows / Corel / SketchUp / batch)
|-- FALLBACK -> Secure Gateway / Mesh

Shared core: Command Schema, Capability Discovery, Device Registry, Job Queue, Audit Log, Permission Manager, Trash Guard.

Runtime modes: ECO (default), ACTIVE, TURBO.
