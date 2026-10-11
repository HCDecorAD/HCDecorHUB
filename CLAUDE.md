# CLAUDE.md — Bản đồ hệ thống

## 1. Xưng hô và nguyên tắc giao việc
- Người dùng xưng "anh", Claude xưng "em".
- Khi được giao việc thì tự động làm hết, không hỏi lại từng bước.
- Chỉ dừng lại hỏi khi việc không đảo ngược được, hoặc khi cần token / mật khẩu.

## 2. Hệ thống
### Auto 24/7
- Luồng chạy: issue có `@claude` → Claude sửa code trên nhánh → tự mở PR → chờ kiểm tra bắt buộc → tự squash-merge.
- Workflow trung tâm nằm ở repo `HCDecorAD/auto-247`.
- Mỗi dự án chỉ có đúng một file caller: `.github/workflows/auto-247.yml`.

### HCDR Relay
- Repo private `HCDecorAD/HCDecor-HCDR-Relay`, dùng để điều khiển máy HOCUONG.
- Cách chạy: tạo issue có label `hcdr-job` → agent chạy tool nằm trong allowlist → trả kết quả bằng comment.
- Tuyệt đối không đưa token hay secret vào nội dung job.

### Trình duyệt
- Dùng Chrome (Claude in Chrome) cho các bước cần thao tác giao diện web.

### Kho mã GitHub `HCDecorAD`
- `HCDecorHUB`, `GSC`, `AMONguyen`.
- Nhánh mặc định của cả ba: `main`.

## 3. Cách làm việc
- Tự động hóa tối đa.
- Tự tìm công cụ; khi một cách bị chặn thì thử cách xử lý khác.
- Tra cứu tài liệu chính thức trên mạng trước khi viết code hoặc tạo plugin.
- Việc quan trọng (đụng production, tiền, dữ liệu khách): ghi `[manual]` vào issue. Khi đó chỉ mở PR, chủ repo tự merge.
- Báo cáo ngắn gọn.

## 4. Giới hạn cứng, không lách
- Không nhập hoặc nhận token / mật khẩu; anh tự dán vào secret.
- Nếu hệ thống chặn một nút như Merge hay Commit thì dừng lại và nói rõ, không tìm đường vòng.
- Quy tắc bắt buộc tuyệt đối thì đặt thành hook, không chỉ ghi trong file này.
- Không ghi số điện thoại, email hay thông tin cá nhân vào file.
