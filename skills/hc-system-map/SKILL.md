# HC System Map — bản đồ hệ thống HCDecor

Version: 2026-10-12
Status: ACTIVE
Áp dụng: mọi chat trong dự án HCDecor HUB và mọi repo `HCDecorAD/*` khi làm việc với AMO, GSC, HCDecorHUB.

## 1. Cách làm việc chung
- Người dùng xưng "anh", Claude xưng "em", trả lời tiếng Việt.
- Được giao việc thì tự làm hết, không hỏi lại từng bước.
- Chỉ dừng khi: (a) thao tác không đảo ngược được, (b) ảnh hưởng hệ thống live/production, (c) cần token, mật khẩu, hoặc quyền mà anh phải tự cấp.
- Không nhập hoặc nhận token/mật khẩu trong chat. Anh tự dán vào secret của repo/host.
- Nếu hệ thống chặn một nút (Merge, Commit, Deploy) thì dừng và nói rõ lý do. Không tìm đường vòng.
- Báo cáo ngắn: kết quả, bằng chứng (link PR, run, mã thoát), một bước tiếp theo.

## 2. Các hệ thống

### AMO (cửa hàng amonguyen.com)
- Repo: `HCDecorAD/AMONguyen`. Nhánh mặc định: `main` (có bảo vệ, bắt buộc check `editor-contract`).
- Backend: Cloudflare Worker `hc-shop-engine`, D1 `hcs`, store `store_amo`.
- Frontend tĩnh: GitHub Pages (amonguyen.com). Trang quản trị: `shop-admin.html`.
- Xác thực: mã thành viên `mem_<id>.<secret>`, chỉ lưu hash SHA-256. Vai trò: viewer, staff, manager, owner. Superadmin dùng token riêng.
- Deploy: workflow `update.yml` (workflow_dispatch trên `main`). Đây là thao tác ảnh hưởng live, cần anh xác nhận trước.

### GSC
- Repo: `HCDecorAD/GSC`. Có `CLAUDE.md` riêng; giao việc code bằng issue có `@claude`, quy trình Auto 24/7 tự mở PR và tự squash-merge trừ việc đánh dấu `[manual]`.

### HCDecorHUB
- Repo: `HCDecorAD/HCDecorHUB`. Nhánh mặc định: `main`.
- Có `CLAUDE.md`, `AGENTS.md`, thư mục `skills/`.
- Quy tắc cứng HOCUONG EXECUTION: việc chạy trên máy HOCUONG (BAT, PowerShell, build, deploy, đọc log) dùng MCP `hc-imaster-mesh` nếu phiên có, không thì dùng HCDR Relay (skill `hcdr-relay`). Relay chỉ làm việc trong `D:\HCDecorHUB`. Khóa `production_mutation` chỉ anh mở.

### Hạ tầng dùng chung
- Auto 24/7: workflow trung tâm ở `HCDecorAD/auto-247`. Mỗi repo chỉ có một file gọi `.github/workflows/auto-247.yml`.
- HCDR Relay: `HCDecorAD/HCDecor-HCDR-Relay` (repo private). Điều khiển máy HOCUONG bằng issue có label `hcdr-job`. Không đưa token hay secret vào nội dung job.

## 3. Quy trình git và PR
- Luôn tạo nhánh mới từ `main`. Không push thẳng lên `main`.
- Trước khi đẩy nhánh, fetch lại `main` để tránh lỗi shallow clone (HTTP 413).
- Mở PR bằng REST API (`gh api repos/<owner>/<repo>/pulls`) với body JSON. GraphQL có thể bị chặn trong phiên Claude Code.
- Chờ check bắt buộc xanh (`editor-contract` với AMO) rồi mới merge.
- Commit message kết thúc bằng:
  `Co-Authored-By: Claude Haiku 5.5 <noreply@anthropic.com>`
  `Claude-Session: <link phiên>`
- Mô tả PR kết thúc bằng: `🤖 Generated with [Claude Code](https://claude.com/claude-code)`

## 4. Ranh giới an toàn
- Việc đụng production, tiền, hoặc dữ liệu khách: ghi `[manual]` trong issue, chỉ mở PR, anh tự merge.
- Không tắt check, không bỏ qua test, không commit rỗng để chạy lại CI, không đóng rồi mở lại PR chỉ để kích CI.
- Không ghi số điện thoại, email, token, mật khẩu vào repo hay memory.
- Không suy đoán trạng thái: mỗi trạng thái PASS phải có bằng chứng (kết quả API, log, mã thoát, link).

## 5. Chat trong dự án HCDecor HUB
- Tài liệu dự án nằm trong Project docs (ví dụ `claude/hc-admin-ban-giao.md`, `claude/ban-do-he-thong.md`). Đọc tài liệu liên quan trước khi trả lời câu hỏi về hệ thống.
- Thay đổi có giá trị lâu dài (quyết định, trạng thái hệ thống) thì ghi lại vào Project doc tương ứng.
