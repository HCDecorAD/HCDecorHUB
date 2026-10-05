# iMaster Infrastructure & Remote Operations Skills

Version: 2026-10-05
Status: ACTIVE

## Purpose
Bộ skill rút ra từ quá trình triển khai thực tế ChatGPT → Secure MCP Tunnel → Local MCP → Transport Mesh → Gateway → Zeus → HOCUONG. Mục tiêu: tái sử dụng cho setup, build, deploy, debug, recovery và điều khiển máy local.

## Skill 1 — Secure MCP Tunnel Operator [HARD]
- Triển khai end-to-end: ChatGPT → Secure Tunnel → Local MCP → Mesh → Gateway → HOCUONG.
- Phân biệt full tunnel-client với runtime bundle.
- Tạo profile bằng sample hợp lệ; chạy doctor; run hidden; autostart.
- Không gọi DONE cho tới khi ChatGPT gọi MCP tool thật và nhận result từ HOCUONG.
- Acceptance: WORKS → ONE REAL TARGETED TEST → PASS → USE/DONE.

## Skill 2 — Runtime Evidence Before DONE [HARD]
- CODE DONE ≠ BUILD DONE ≠ CORE PASS ≠ PRODUCT DONE.
- DONE chỉ khi người dùng dùng được thực tế.
- Minimum proof: one real targeted runtime test.
- Không tạo ceremony thừa sau khi đã đủ bằng chứng.

## Skill 3 — Remote Setup Ownership [HARD]
- Có Mesh/HCDR/local executor thì AI tự thao tác máy.
- Không biến owner thành remote hands.
- Chỉ gọi owner khi thật sự cần login, credential input, authorization, security confirmation hoặc irreversible boundary.
- HCDR là fallback/recovery; Mesh/local-direct là đường chính khi healthy.

## Skill 4 — Transport Failure Classifier
Phân loại lỗi trước khi sửa:
- APPLICATION_ERROR
- HCDR_PAYLOAD_ERROR
- SHELL_ESCAPING_ERROR
- CREDENTIAL_ENV_ERROR
- TUNNEL_CONFIG_ERROR
- MCP_ATTACH_ERROR
- CHATGPT_TOOL_DISCOVERY_ERROR
Không gom tất cả thành “Gateway lỗi”. Sửa đúng layer, giữ nguyên các layer đã PASS.

## Skill 5 — Windows Safe Script Executor [HARD]
- NO_COMPLEX_INLINE_POWERSHELL_OVER_TRANSPORT.
- Gặp PowerShell variables, JSON, pipe, nested quotes, nhiều statement hoặc nhiều interpreter boundary → FILE_MODE_REQUIRED.
- Viết .ps1 rồi chạy:
  powershell.exe -NoProfile -ExecutionPolicy Bypass -File <script.ps1>
- Dùng absolute path; không giả định HCDR cwd.
- Secrets không nằm trong job body, logs, command strings hoặc source repo.

## Skill 6 — Capability Acquisition & Route Escalation
Khi thiếu capability:
DISCOVER_EXISTING → SEARCH_TRUSTED/OFFICIAL → ACQUIRE/INSTALL/ADAPT → VERIFY → EXECUTE → REAL TEST → STORE LEARNING.
Thiếu tool không đồng nghĩa hard blocked.
Route ưu tiên:
existing local/Mesh → installed connector/plugin → official CLI/API/SDK → trusted OSS → HCDR fallback → browser/UI authorization boundary.

## Architecture Law — Replaceable Transport, Stable Core
Transport phải thay được; Core không phụ thuộc transport.
- Core: iMaster / Mesh / Gateway / Zeus / Local Executor.
- Transport adapters: Secure Tunnel, HCDR và các carrier tương lai.
- Một transport hỏng → đổi/recover transport; không rebuild core.
- ONE_FAILED_TRANSPORT ≠ HARD_BLOCKED.

## Operational Laws learned
1. MINIMUM_SUFFICIENT_PROOF_THEN_SHIP.
2. DO_NOT_REDO_PASS.
3. PRESERVE_CHECKPOINT_ACROSS_RECOVERY.
4. LOCAL_FIRST.
5. BUILD_FAST_INSIDE — PROTECT_AT_BOUNDARY.
6. OWNER_ACTION_REQUIRED chỉ dùng cho boundary thật.
7. Runtime/parser/transport failure không được gán nhầm thành application failure.
8. Khi command crossing shell/serialization boundaries trở nên phức tạp, chuyển sang file execution trước khi retry.
9. Custom MCP product acceptance chỉ PASS khi current ChatGPT session nhìn thấy tool và gọi tool thật thành công.
10. Sau khi PASS, lưu skill/rule để lần sau không học lại từ đầu.

## Proven reference acceptance
Ngày 2026-10-05, HC iMaster Mesh đã thực hiện real MCP calls từ ChatGPT:
- mesh_health → ok:true; active: local-direct
- mesh_status → gateway: UP; Zeus Z06 ok:true
- list_chat_tabs → trả về các ChatGPT tabs thật trên HOCUONG
Đây là mẫu acceptance chuẩn cho các gateway tương tự.
