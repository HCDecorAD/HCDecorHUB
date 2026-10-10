# HC Content Studio — Module đăng bài đa kênh (phương án 0đ)

Trạng thái: BẢN THIẾT KẾ, chưa nối production. Mọi publish đều tắt mặc định.

## Luồng

Lệnh → Phân tích (AI) → Tạo bài + media → Duyệt → Lịch (Metricool) → Đăng → Đối soát

| Module | Vai trò | Tái sử dụng | Mới |
|---|---|---|---|
| M1 Cửa nhận lệnh | Nhận yêu cầu, ghi brief | Chat HCDR/Mesh; form hub | Cửa nhập brief |
| M2 Phân tích & viết nháp | Sinh 2–3 phương án/kênh, kiểm tra quy tắc | — | Có |
| M3 Nội dung & media | Chọn ảnh, dựng video đơn giản, chỉnh theo kênh | Workflow `media-check`, `channel-adapt`; Creative Factory (chưa xác minh) | Ít |
| M4 Duyệt | Người duyệt từng bài; ghi `production_approved` | Cờ approval của `social-manager`; Review Center (chưa xác minh) | Giao diện nhỏ |
| M5 Lịch | Đặt lịch đa kênh, chọn giờ tốt nhất | Metricool | Không |
| M6 Đăng & đối soát | Theo dõi trạng thái, báo lỗi, chống đăng trùng | Metricool; log của `social-manager` | Ít |

## Quy tắc không thương lượng

1. Không bao giờ đăng công khai khi chưa có duyệt của người (`production_approved = true`, kèm người duyệt và thời điểm).
2. AI chỉ tạo nháp. Không tự thêm giá, khuyến mãi, số liệu khi brief không có.
3. Lỗi đăng không tự thử lại khi chưa đối soát xem bài đã lên hay chưa (chống đăng trùng).
4. Giữ nguyên file media gốc; không ghi đè.
5. Mọi token/khóa API chỉ nằm trong biến môi trường, không lưu trong repo, Sheets hay chat.
6. Có công tắc dừng khẩn cấp: `publish_enabled: false` trong config.

## Ngăn xếp 0đ

| Việc | Công cụ | Ghi chú |
|---|---|---|
| AI viết bài | Ollama local (ưu tiên) hoặc Gemini free tier | Kiểm tra hạn mức trước khi chốt |
| Ảnh | Drive (ảnh thật) + Pexels API (free) | Kiểm tra giấy phép từng file |
| Video | FFmpeg (slideshow + chữ + nhạc có giấy phép) | Không dùng video AI sinh tự động |
| Điều phối | Task Scheduler hoặc n8n Community | Chạy khi máy bật |
| Duyệt | Telegram bot (nút Duyệt / Sửa / Bỏ) | Chỉ chat của chủ |
| Lên lịch & đăng | Metricool | Gói free: kiểm tra giới hạn số bài/kênh |

Chưa xác minh (không được coi là đã có): gói Metricool hiện tại, Zalo OA (chưa xác minh trên Metricool), Creative Factory, Review Center, hàm `hcdecor_auto_enqueue`.

## Giới hạn hiện có cần quyết định

- `social-manager.php` chỉ cho lên lịch trong vòng 10 phút sau khi duyệt. Cần nới nếu muốn đặt lịch nhiều ngày. Module này KHÔNG sửa file đó trong bản này.
- Hai đường đăng đang tồn tại: Metricool (theo chính sách) và gọi API trực tiếp từ WordPress. Module này chỉ dùng Metricool.

## Việc cần người làm (một lượt)

1. Metricool: kiểm tra gói, kết nối lại kênh nếu cần, lấy API token.
2. Telegram: tạo bot qua @BotFather, nhắn `/start`, gửi chat ID.
3. AI: cài Ollama và tải model nhỏ, hoặc tạo khóa Gemini.
4. Pexels: tạo khóa API.
5. Chọn Page Facebook đích và kênh ưu tiên.
6. Xác nhận quyền ghi vào repo (branch mới) để đưa module vào mã nguồn.

## Bước tiếp theo

- Viết M1 (form/lệnh nhập brief) và M2 (prompt + kiểm tra quy tắc) dưới dạng script độc lập, chạy được trên máy, trước khi nối vào WordPress.
- Chạy thử toàn luồng ở chế độ nháp (dry run), chưa đăng.
