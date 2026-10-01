# AutoChat V1 UI contract

## Left pane
Shows live ChatGPT CDP pages. Only pages with a non-empty conversation_id may be bound. Root ChatGPT pages display ROOT and cannot authorize commands.

## Binding
Choose alias then select a real conversation and Bind. Registry prevents one conversation from owning multiple aliases and prevents silent alias reassignment.

## Commands
V1 staging exposes Dry Run only. It requires:
1. global queue not paused;
2. selected alias not paused;
3. alias registered;
4. current live page resolves by exact conversation_id.

## Safety controls
- STOP ALL persists.
- Pause/Resume is per alias.
- Unbind removes routing authority.
- Browser title is display metadata only.
- target_id is ephemeral metadata only.

## Theme
Dark, Light, System are available. Theme styling remains intentionally lightweight in Tkinter V1.
