Part 1 — Logic & UX/UI
[Critical] Blade templates gây lỗi render (500) theo log

Location: app.blade.php (line 261), app.blade.php (line 627), image-generator.blade.php (line 461), laravel.log
Short: Log ghi nhận lỗi Blade “unexpected end of file/else/endif” làm trang không render.
Details: Log ngày 2026-01-25/26 báo ViewException ở layout và image-generator; lỗi dạng này sẽ chặn toàn bộ UI.
Steps: 1) Mở trang bất kỳ dùng layout hoặc Studio; 2) Khi Blade mismatch xảy ra, page trả 500.
Expected: Trang render bình thường.
Actual: 500 kèm lỗi Blade parse.
Fix: Soát cặp @if/@endif, @auth/@endauth, @foreach/@endforeach; chạy php artisan view:clear và thêm bước CI php artisan view:cache để bắt lỗi sớm.
[Low] Polling lịch sử ảnh chạy liên tục dù không cần

Location: user-style-history.blade.php (line 11)
Short: wire:poll.5s tạo request nền không cần thiết.
Details: Khi user chỉ xem Studio nhưng không generate, Livewire vẫn polling 5s/lần.
Steps: 1) Mở trang Studio; 2) Không tạo ảnh; 3) Quan sát network có request Livewire mỗi 5s.
Expected: Polling chỉ bật khi đang generate hoặc theo event.
Actual: Polling luôn bật.
Fix: Gating polling theo trạng thái generate hoặc thay bằng event imageGenerated.
Part 2 — API & Backend
[High] Endpoint debug public lộ dữ liệu models

Location: web.php (lines 30-39)
Short: /debug/models mở công khai, trả JSON danh sách model/provider.
Details: Endpoint này lộ thông tin nội bộ, không phù hợp production.
Steps: 1) Truy cập /debug/models khi chưa đăng nhập; 2) Nhận JSON.
Expected: 403/404 hoặc chỉ chạy ở local.
Actual: JSON trả về đầy đủ.
Fix: Gắn middleware admin, hoặc bật điều kiện app()->environment('local') rồi return 404.
[High] DB thiếu cột deleted_at gây lỗi truy vấn

Location: 2026_01_24_132004_add_deleted_at_to_generated_images_table.php, laravel.log
Short: Log ghi Unknown column generated_images.deleted_at.
Details: Soft deletes đã dùng trong model nhưng DB chưa migrate đầy đủ.
Steps: 1) Chạy app trên DB chưa migrate; 2) Truy cập trang dùng GeneratedImage; 3) Lỗi SQL.
Expected: Trang tải bình thường.
Actual: SQLSTATE 42S22.
Fix: Bắt buộc migrate trên deploy; thêm health-check xác nhận migration đã chạy.
[High] Ảnh lưu với ACL public có thể lộ dữ liệu

Location: StorageService.php (line 71)
Short: Storage::put(..., 'public') lưu ảnh public.
Details: UI nói link có hạn, nhưng ACL public khiến ai có URL đều truy cập được lâu dài.
Steps: 1) Tạo ảnh; 2) Dùng URL trực tiếp từ storage; 3) Ảnh truy cập công khai.
Expected: Ảnh private + chỉ truy cập qua presigned URL.
Actual: Ảnh public (phụ thuộc bucket policy).
Fix: Lưu private, chỉ dùng temporaryUrl/proxy để tải.
[Medium] Internal API adjust wallet không idempotent

Location: InternalApiController.php (lines 34-75)
Short: Gọi /api/internal/wallet/adjust nhiều lần với reference_id giống nhau sẽ cộng/trừ lặp.
Details: Không có check trùng hoặc unique index cho trường hợp reference_id.
Steps: 1) Gọi endpoint 2 lần cùng reference_id; 2) Credits thay đổi 2 lần.
Expected: Lần 2 bị từ chối hoặc trả “already processed”.
Actual: Credits thay đổi lặp.
Fix: Thêm unique index (source, reference_id) và check trước khi tạo transaction.
[Low] Download proxy tải toàn bộ file vào memory

Location: HistoryController.php (lines 130-149)
Short: Http::get()/Storage::get() đọc toàn bộ ảnh vào RAM.
Details: Ảnh lớn có thể gây spike memory.
Steps: 1) Download ảnh lớn; 2) Quan sát memory tăng.
Expected: Stream file.
Actual: Load toàn bộ rồi trả response.
Fix: Dùng response streaming hoặc Storage::download.
[Low] Không có UI clear OpenRouter API key

Location: SettingsController.php (lines 45-52)
Short: Để trống không xóa key đã lưu.
Details: filled() bỏ qua cập nhật khi input rỗng.
Steps: 1) Lưu API key; 2) Submit rỗng; 3) Key vẫn tồn tại.
Expected: Có thể xóa key.
Actual: Key giữ nguyên.
Fix: Thêm nút “Clear key” hoặc cho phép empty => delete.
API Inventory (Backend + External)

Endpoint	Mục đích	Auth	Required	Optional	Output
GET /api/user	Lấy info user hiện tại	Sanctum	none	none	{id,name,email,credits}
POST /api/internal/wallet/adjust	Cộng/trừ credits nội bộ	X-API-Secret	user_id, amount, reason	source, reference_id	{success, transaction_id, new_balance}
POST /api/internal/payment/callback	Callback VietQR	X-API-Secret	user_id, amount, transaction_ref	none	{success, transaction_id, new_balance}
GET {openrouter_base}/models	Lấy models	Bearer	none	none	JSON models
POST {openrouter_base}/chat/completions	Tạo ảnh	Bearer	model, messages, modalities	image_config	choices[0].message.images
GET {openrouter_base}/key	Check balance	Bearer	none	none	API key info
GET https://api.vietqr.io/image/...	QR nạp tiền	none	params trong URL	amount	image/QR
Part 3 — Image & OpenRouter
[High] update Style không validate model image-capable

Location: StyleController.php (lines 203-212)
Short: Admin có thể lưu model text-only hoặc model đã bị xoá.
Details: openrouter_model_id chỉ check string khi update, không check list image-capable.
Steps: 1) Edit Style; 2) Set model text-only; 3) Generate.
Expected: Chặn ngay khi save hoặc trước khi generate.
Actual: Request OpenRouter trả 400/404 (log có).
Fix: Thêm validation giống store và guard ở runtime.
[High] Không có guard runtime cho model không hỗ trợ image

Location: OpenRouterService.php (lines 342-398), ImageGenerator.php (lines 216-279)
Short: Vẫn gửi modalities: ["image","text"] cho model không image-capable.
Details: Khi model bị gỡ hoặc không hỗ trợ, lỗi xảy ra ở OpenRouter.
Steps: 1) Chọn model không image-capable; 2) Generate.
Expected: Báo lỗi sớm trên server.
Actual: Fail ở OpenRouter với 400/404.
Fix: So sánh openrouter_model_id với ModelManager trước khi gọi API.
[High] Base URL cấu hình không ép /api/v1

Location: OpenRouterService.php (lines 31-39), SettingsController.php (lines 55-65)
Short: Nhập base URL thiếu /api/v1 dẫn đến 404.
Details: Service chỉ rtrim('/'), không auto-append /api/v1.
Steps: 1) Set base URL = https://openrouter.ai; 2) Refresh models.
Expected: Tự sửa thành .../api/v1.
Actual: 404 do gọi /models sai đường dẫn.
Fix: Normalize hoặc validate bắt buộc /api/v1.
[Medium] Không giới hạn tổng ảnh (user + system) và overhead base64

Location: ImageGenerator.php (lines 568-579), OpenRouterService.php (lines 398-434)
Short: Vượt giới hạn payload của provider dễ gây 400/413.
Details: Chỉ tính dung lượng ảnh user; system images + base64 overhead chưa tính.
Steps: 1) Tạo style với 5 system images + 10 slot; 2) Upload đủ; 3) Generate.
Expected: Validation chặn vượt giới hạn.
Actual: Request gửi đi và fail ở provider.
Fix: Tính tổng bytes (base64) + số ảnh; cấu hình per-model limit.
[Medium] Fallback model allowlist có thể chứa model không image-capable

Location: services_custom.php (lines 42-54), OpenRouterService.php (lines 173-206)
Short: Fallback có thể trỏ model không hỗ trợ image => 400.
Details: Log ngày 2026-01-24 có lỗi “Model does not support modalities”.
Steps: 1) API /models thiếu output_modalities; 2) Admin chọn model từ fallback; 3) Generate.
Expected: Chỉ hiển thị model image-capable.
Actual: Cho phép model không hỗ trợ image.
Fix: Duy trì allowlist chính xác theo /models hoặc cache danh sách từ API.
[Low/UX] Thông báo lỗi async quá chung chung

Location: ImageGenerator.php (lines 461-463)
Short: UI luôn báo “credits đã hoàn lại”, không hiển thị lý do thật.
Details: GeneratedImage->error_message không được show ngay khi fail.
Steps: 1) Generate bị provider từ chối; 2) Xem thông báo.
Expected: Hiển thị lý do (từ error_message).
Actual: Thông báo chung chung.
Fix: Hiển thị error_message khi status failed.
Part 4 — Tích hợp & đồng bộ
[Medium] Style bị tắt vẫn có thể generate từ tab cũ

Location: ImageGenerator.php (lines 216-236)
Short: Không kiểm tra is_active trước khi generate.
Details: Studio mở trước khi admin disable; user vẫn tạo ảnh.
Steps: 1) Mở Studio style; 2) Admin disable; 3) User generate.
Expected: Bị chặn ngay.
Actual: Vẫn gửi request.
Fix: Check style->is_active trong generate() và trong job.
[Medium] Xóa ảnh ở Admin không cleanup file nếu storage_path là URL

Location: GeneratedImageController.php (lines 83-86)
Short: Nếu lưu URL (presigned/custom), file không bị xóa.
Details: Admin delete chỉ xóa khi path không bắt đầu bằng http.
Steps: 1) Có ảnh với storage_path là URL; 2) Delete trong Admin.
Expected: File bị xóa.
Actual: File còn trên storage.
Fix: Dùng logic parse URL như HistoryController::destroy.
[Medium] Watchdog timeout + job failure có thể double-refund

Location: ImageGenerator.php (lines 466-489), GenerateImageJob.php (lines 97-113), GenerateImageJob.php (lines 155-183)
Short: Refund được gọi từ cả watchdog và job failure.
Details: Đã có unique index nhưng vẫn log lỗi và UI báo refund không chắc chắn.
Steps: 1) Job chậm > 5 phút; 2) Watchdog refund; 3) Job fail sau đó.
Expected: 1 lần refund, UI phản ánh đúng.
Actual: Refund attempt lặp, log lỗi; UI luôn nói đã hoàn tiền.
Fix: Thêm refunded_at/refund_tx_id trên GeneratedImage và guard trước khi refund.
[Low/ops] Expiry ảnh phụ thuộc scheduler

Location: Kernel.php (lines 13-19), CleanupOrphanImages.php
Short: Nếu cron không chạy, ảnh không hết hạn như UI thông báo.
Details: UI nói ảnh hết hạn theo image_expiry_days.
Steps: 1) Deploy không chạy scheduler; 2) Chờ quá hạn.
Expected: Ảnh bị soft-delete.
Actual: Ảnh vẫn tồn tại.
Fix: Bật cron cho scheduler và thêm health-check.
Testing Gaps

Không có test cho image generation flow, OpenRouter adapter, refund/idempotency, cleanup command, internal APIs; rủi ro regression cao.
Gợi ý thêm test: generation success/fail + refund idempotent; internal API auth; model validation; cleanup command dry-run.
Next steps gợi ý

Ưu tiên fix các lỗi Critical/High: Blade parse, debug route, model validation, base URL normalize, storage privacy.
Chạy php artisan view:clear và migrate đầy đủ DB trước khi test lại.
Thêm test tối thiểu cho: generation fail/refund, internal API idempotency, model validation.