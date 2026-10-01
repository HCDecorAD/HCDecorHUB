# CDP read-only observer

The observer allowlist is intentionally narrow: Runtime.enable and Runtime.evaluate.

Input.*, Page.navigate, DOM mutation, and Runtime.callFunctionOn are blocked by policy. The observer may inspect page state but cannot type, click, navigate, or authorize Send.

Selectors are treated as best-effort observations and must degrade to UNKNOWN when the ChatGPT UI changes.
