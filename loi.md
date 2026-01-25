(routes/controllers/services/models/Livewire/views/migrations/config). Dưới đây là báo cáo theo đúng 4 phần, kèm inventory API và đầy đủ Expected/Actual.

API Inventory

GET /api/user — Auth: auth:sanctum; Purpose: lấy thông tin user hiện tại; Input: none; Output: {id,name,email,credits}.
POST /api/internal/wallet/adjust — Auth: header X-API-Secret; Purpose: cộng/trừ credits; Input: user_id, amount (≠0), reason*, source?, reference_id?; Output: {success, transaction_id, new_balance} hoặc {error} (401/422).
POST /api/internal/payment/callback — Auth: header X-API-Secret; Purpose: callback VietQR; Input: user_id, amount (>=1), transaction_ref*; Output: {success, transaction_id?, new_balance, message?}.
Ghi chú: Các API internal hiện không được gọi từ frontend trong repo (dùng cho hệ thống ngoài như VietQR/bot).
Part 1 — Logic & UX/UI Issues

UX-01: Hiển thị giá/credits bị làm tròn, lệch so với thực tế

Location: image-generator.blade.php, home.blade.php, app.blade.php, index.blade.php
Severity: Medium
Short/Detail: UI dùng number_format(..., 0) nên làm tròn số; backend dùng decimal (2 chữ số) → user thấy khác số bị trừ.
Steps: đặt Style->price = 1.5; vào home/studio xem giá; tạo ảnh; kiểm tra credits bị trừ 1.5 nhưng UI hiển thị 1 hoặc 2.
Expected vs Actual: Expected hiển thị chính xác 1.50 và số dư tương ứng; Actual hiển thị số nguyên bị làm tròn.
Fix: hiển thị 2 chữ số hoặc ép toàn hệ thống dùng integer credits và validate đồng bộ.
UX-02: Thông báo “link còn hiệu lực X ngày” sai thực tế

Location: image-generator.blade.php, GeneratedImage.php
Severity: Medium
Short/Detail: UI nói link còn image_expiry_days (mặc định 30) nhưng URL dùng temporaryUrl() chỉ valid 7 ngày.
Steps: tạo ảnh → copy share link → chờ >7 ngày → link hỏng dù UI nói 30 ngày.
Expected vs Actual: Expected link sống đúng số ngày hiển thị; Actual link hết hạn sớm hơn.
Fix: hiển thị đúng 7 ngày hoặc dùng route proxy/URL public theo đúng hạn lưu ảnh.
UX-03: Lưu selected_options mất group name, Admin xem sai

Location: ImageGenerator.php (save array_values), show.blade.php
Severity: Medium
Short/Detail: selected_options lưu dạng list ID nên Admin hiển thị 0: #123 thay vì Group: Option.
Steps: tạo ảnh chọn options nhiều nhóm → vào Admin > Image detail → xem Selected Options.
Expected vs Actual: Expected hiển thị tên nhóm + option; Actual hiển thị index số.
Fix: lưu group_name => option_id, hoặc join ngược trong view theo ID để hiển thị label.
UX-04: Sanitization làm biến đổi prompt (mất dấu ngoặc/ký tự)

Location: ImageGenerator.php (validateGenerationInputs)
Severity: Low
Short/Detail: htmlspecialchars(strip_tags()) đổi dấu " ' < > thành entity, ảnh hưởng chất lượng prompt.
Steps: nhập prompt có dấu ngoặc kép; tạo ảnh; xem prompt ở admin.
Expected vs Actual: Expected prompt giữ nguyên ký tự; Actual bị encode (&quot;).
Fix: chỉ strip_tags, hoặc sanitize khi render; giữ nguyên text cho model.
UX-05: Nạp tiền thiếu input số tiền, khó thao tác

Location: index.blade.php, User::getVietQRUrl
Severity: Low
Short/Detail: Chỉ có QR tĩnh, user phải tự nhập số tiền ở app ngân hàng; không có input/preview.
Steps: vào Wallet → muốn nạp 50k → không có form nhập số tiền.
Expected vs Actual: Expected có ô nhập số tiền → QR cập nhật; Actual QR tĩnh.
Fix: thêm input amount + regenerate QR, hoặc hướng dẫn rõ cách nhập thủ công.
Part 2 — API & Backend Issues

SEC-01: .env chứa secrets thật + debug bật

Location: .env
Severity: Critical
Short/Detail: File chứa API key OpenRouter, MinIO creds, và APP_DEBUG=true; rủi ro lộ dữ liệu nếu repo public/backup.
Steps: mở .env trong repo.
Expected vs Actual: Expected .env không commit; dùng .env.example và secret manager; debug off prod. Actual secrets nằm trong repo.
Fix: xóa .env khỏi VCS, rotate keys, đảm bảo .gitignore và config prod tắt debug.
API-02: openrouter_base_url có thể bị lưu rỗng → call lỗi

Location: SettingsController.php, OpenRouterService.php
Severity: Medium
Short/Detail: nếu admin xóa base URL và lưu, hệ thống dùng '' → POST /chat/completions sai domain.
Steps: Settings → clear base URL → save → generate image.
Expected vs Actual: Expected fallback về URL mặc định; Actual request lỗi vì base URL rỗng.
Fix: validate required|url, hoặc nếu empty thì xóa setting/đặt default.
API-03: checkBalance() hardcode URL, bỏ qua base_url

Location: OpenRouterService.php::checkBalance
Severity: Medium
Short/Detail: gọi https://.../api/v1/key trực tiếp, không dùng cấu hình base URL.
Steps: set base URL sang proxy → vào admin dashboard → check balance vẫn gọi host mặc định.
Expected vs Actual: Expected dùng base URL cấu hình; Actual dùng URL hardcode.
Fix: dùng $this->baseUrl . '/key' (hoặc endpoint đúng theo docs).
API-04: Storage chấp nhận dữ liệu không phải ảnh

Location: StorageService.php
Severity: Medium
Short/Detail: nếu finfo không nhận dạng MIME, code default png và lưu; không reject non-image.
Steps: gọi saveBase64Image() bằng base64 text; file vẫn được lưu.
Expected vs Actual: Expected reject non-image; Actual lưu file giả dạng .png.
Fix: nếu MIME không bắt đầu bằng image/ thì trả lỗi.
API-05: Download ảnh load toàn bộ file vào RAM

Location: HistoryController.php::download
Severity: Medium
Short/Detail: dùng Http::get()/Storage::get() → file lớn làm tốn RAM, dễ OOM.
Steps: lưu ảnh rất lớn (4K/8K) → download.
Expected vs Actual: Expected stream file; Actual load toàn bộ vào memory.
Fix: dùng readStream() + response()->streamDownload().
Part 3 — Tạo ảnh & OpenRouter

OR-01: SSRF qua URL ảnh trong response (không chặn IP nội bộ)

Location: BaseAdapter.php::downloadImageAsBase64
Severity: High
Short/Detail: Adapter fetch bất kỳ URL nào mà model trả về; không chặn localhost/private IP.
Steps: giả lập response có image_url.url = http://127.0.0.1/... → adapter fetch.
Expected vs Actual: Expected chặn private/local; Actual request vẫn xảy ra.
Fix: áp dụng SSRF guard giống OpenRouterService::downloadImageAsBase64 (block private/reserved, allowlist).
OR-02: Retry 429/5xx có thể không hoạt động

Location: OpenRouterService.php::clientForPost
Severity: Medium
Short/Detail: retry() chỉ chạy trên exception; nếu không throw(), 429/5xx có thể không retry tùy phiên bản Laravel.
Steps: test giả lập 429; quan sát request chỉ 1 lần.
Expected vs Actual: Expected retry/backoff 2–3 lần; Actual có thể không retry.
Fix: gọi ->throw() trước khi xử lý hoặc dùng retry với điều kiện trên response.
OR-03: 4K image có thể vượt giới hạn 20MB

Location: image-generator.blade.php, StorageService.php
Severity: Medium
Short/Detail: UI cho chọn 4K nhưng StorageService max 20MB → ảnh lớn fail lưu.
Steps: chọn Gemini 4K → generate ảnh phức tạp → lỗi “Image too large”.
Expected vs Actual: Expected 4K lưu được hoặc bị chặn sớm; Actual fail sau khi gọi API.
Fix: nâng maxBytes, nén (WebP), hoặc chặn 4K với cảnh báo.
OR-04: Không giới hạn tổng size ảnh input (nhiều slot)

Location: ImageGenerator.php
Severity: Low/Medium
Short/Detail: mỗi ảnh 10MB nhưng nhiều slot có thể vượt payload limit → fail khó hiểu.
Steps: cấu hình 8–10 slots, upload max size; generate.
Expected vs Actual: Expected validation tổng size; Actual fail ở bước API/storage.
Fix: giới hạn tổng size hoặc giảm số slot/size per slot.
Part 4 — Tích hợp & Đồng bộ

INT-01: Watchdog timeout có thể refund rồi job vẫn hoàn thành

Location: ImageGenerator.php::pollImageStatus, GenerateImageJob.php::handle
Severity: High
Short/Detail: watchdog đánh failed + refund sau 5 phút; job đang chạy vẫn có thể markAsCompleted → free image.
Steps: bật async queue; cố tình làm job >5 phút (delay worker); quan sát status đổi từ failed → completed.
Expected vs Actual: Expected job bị dừng hoặc không được ghi đè status; Actual status có thể bị ghi đè.
Fix: trong job, check status vẫn processing trước khi complete; hoặc cancel job khi watchdog kích hoạt.
INT-02: Async mode phụ thuộc worker, không cảnh báo khi worker chết

Location: ImageGenerator.php, queue config
Severity: Medium
Short/Detail: nếu QUEUE_CONNECTION async nhưng worker không chạy, user chờ 5 phút rồi fail.
Steps: set queue = database/redis; stop worker; generate ảnh.
Expected vs Actual: Expected báo lỗi “queue offline” hoặc fallback sync; Actual treo rồi timeout.
Fix: health check queue, fallback sync hoặc hiển thị cảnh báo.
INT-03: credits_used ghi sai khi deduct fail

Location: ImageGenerator.php::generate
Severity: Low/Medium
Short/Detail: record GeneratedImage tạo trước khi deduct; nếu deduct fail, vẫn lưu credits_used = price.
Steps: gây race để deduct fail (2 tab trừ credits đồng thời); xem record failed.
Expected vs Actual: Expected credits_used = 0 khi không trừ; Actual vẫn là giá style.
Fix: set credits_used sau khi deduct thành công hoặc update về 0 trong catch.