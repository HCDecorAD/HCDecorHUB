THEMES={"dark":{"bg":"#10151c","fg":"#f3f6fa"},"light":{"bg":"#f5f7fa","fg":"#111827"}}
def resolve_theme(name,system_dark=False):
 n=(name or "system").lower()
 if n=="system":n="dark" if system_dark else "light"
 if n not in THEMES:raise ValueError(name)
 return n,THEMES[n]
