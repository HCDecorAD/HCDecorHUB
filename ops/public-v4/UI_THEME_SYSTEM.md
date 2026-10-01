# HCDecor UI Theme System v2

Scope: interface configuration only. Production writes remain approval-gated.

## Theme behavior
- Attribute: `data-hc-theme="dark|light"` on `html`.
- First visit: use `prefers-color-scheme`.
- Explicit user choice: persist as `hcdecor-theme` in localStorage.
- Header toggle: adjacent to VN/EN. Accessible label must announce target theme.
- Selected/active UI state: blue accent, never pink.

## Dark
- bg: #090A0B
- surface: #151719
- surface-2: #1C1F22
- text: #EEEAE1
- muted: #A7AAAD
- bronze: #A88B5D
- selected: #2F80ED
- border: rgba(238,234,225,.14)

## Light
- bg: #F5F2EC
- surface: #FFFFFF
- surface-2: #ECE8E0
- text: #151719
- muted: #686B6E
- bronze: #8B7048
- selected: #1769D2
- border: rgba(21,23,25,.14)

## Typography
- Preferred display/UI family: EVO UTM where webfont licensing and files are verified.
- Safe fallback: Arial, sans-serif.
- Do not package/share font files from the workstation.

## Responsive
- Desktop >= 1025
- Tablet 768-1024
- Mobile <= 767
- Tap targets >= 44px.
- Theme toggle must remain visible on mobile header.
- Hero and project media must preserve readable contrast in both themes.

## Elementor
Use CSS classes/tokens instead of per-widget hard-coded colors.
Suggested classes: hc-ui, hc-header, hc-theme-toggle, hc-hero, hc-section, hc-card, hc-project-card, hc-cta.
