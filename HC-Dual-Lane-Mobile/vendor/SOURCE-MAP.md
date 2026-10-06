# Upstream Source Map

Do not copy code until license and version are recorded.

| Upstream | Use | Decision |
|---|---|---|
| LocalSend | LAN discovery / fast transfer design | ADAPT |
| KDE Connect Android | device bridge concepts: clipboard, notifications, share | STUDY/ADAPT |
| scrcpy | Android screen/control over ADB/TCP | USE AS EXTERNAL TOOL |
| Shizuku | optional elevated Android API bridge | OPTIONAL |
| Android MediaStore / SAF | native media/files operations | PRIMARY |
| Microsoft Phone Link / Mobile devices in Explorer | Windows-Samsung bridge | USE PLATFORM |

Rules:
- Keep upstream attribution and licenses.
- Prefer official/native Android APIs for Mobile Agent.
- No root dependency.
- Shizuku is never required for base operation.
