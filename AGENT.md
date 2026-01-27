# AGENT – zdream.vn2 (ZDream/EZShot AI)

Tài liệu này là “bản đồ tư duy” của dự án để hiểu đúng luồng dữ liệu, quy ước code và ràng buộc nghiệp vụ. Hệ thống **đã chuyển hoàn toàn sang BFL/FLUX**, không còn dùng OpenRouter trong runtime (chỉ còn legacy/compat nếu chưa dọn).

---------------------------------------------------------------------------
TỔNG QUAN HỆ THỐNG
---------------------------------------------------------------------------
- Stack: Laravel 10 + Livewire 4 + Tailwind CSS + Alpine (Livewire 4 đã bundle).
- Build frontend: Vite.
- Tạo ảnh AI: **Black Forest Labs (BFL) – FLUX API** (task async + polling).
- Lưu trữ ảnh: MinIO (S3 compatible) cho ảnh tạo ra; public disk cho thumbnail StyleOption.
- Nạp credits: VietQR (chuyển khoản) -> cộng Xu.
- Auth: Laravel Breeze (web) + Sanctum (API).

---------------------------------------------------------------------------
CẤU TRÚC THƯ MỤC / FILE MAP (NHỮNG NƠI QUAN TRỌNG)
---------------------------------------------------------------------------
app/
  Models/
    User, Style, StyleOption, Tag, GeneratedImage, WalletTransaction, Setting
  Services/
    BflService (gọi BFL API + polling + xử lý ảnh)
    ModelManager (cache & group model)
    StorageService (lưu base64 -> MinIO)
    WalletService (cộng/trừ/hoàn Xu, log transaction)
  Livewire/
    ImageGenerator (luồng tạo ảnh chính)
    UserStyleHistory (ảnh gần đây theo style)
  Jobs/
    GenerateImageJob (tạo ảnh async + hoàn tiền khi lỗi)
  Http/Controllers/
    Public: HomeController, StylesController, StudioController,
            HistoryController, WalletController, ProfileController
    Admin: StyleController, StyleOptionController, TagController,
           UserController, TransactionController, GeneratedImageController,
           SettingsController
    Api: InternalApiController
  Http/Middleware/
    AdminMiddleware, EnsureUserIsActive
  Console/
    Commands/CleanupOrphanImages, Kernel (scheduler + watchdog)
  Support/
    Livewire/ComponentRegistryStub (compat Livewire v4 + Ignition)

config/
  services_custom.php (BFL, VietQR, pricing, internal API secret)
  filesystems.php (minio disk)

routes/
  web.php (public + auth + admin)
  api.php (sanctum + internal API)
  web_debug.php (không đăng ký trong RouteServiceProvider)

database/migrations/
  Schema users, styles, style_options, generated_images, wallet_transactions,
  settings, tags, soft deletes, v.v.
  + 2026_01_27_170500_add_generation_params_to_generated_images_table.php
  + 2026_01_27_160000_migrate_openrouter_to_bfl.php (migration dữ liệu sang BFL)

resources/views/
  layouts/app.blade.php (global layout + select2 init)
  livewire/image-generator.blade.php (UI tạo ảnh)
  admin/* (UI admin)
  home, styles, studio, history, wallet, dashboard

resources/js/app.js
  Không import Alpine thủ công (Livewire 4 đã bundle).

html_thuong/
  Prototype tĩnh, không kết nối Laravel.

tài liệu api api.bfl.ai flux/
  Tài liệu BFL FLUX (local docs, dùng để map endpoint/params).

---------------------------------------------------------------------------
ĐỐI TƯỢNG MIỀN & QUY ƯỚC NGHIỆP VỤ
---------------------------------------------------------------------------
1) Style
   - fields chính: name, slug, thumbnail_url, description, price,
     bfl_model_id, base_prompt, config_payload, is_active,
     allow_user_custom_prompt, image_slots, system_images, sort_order, tag_id.
   - slug: tự tạo khi create; KHÔNG tự đổi khi update (tránh vỡ URL).
   - buildFinalPrompt(): ghép base_prompt + options + custom input.
   - MAX_PROMPT_LENGTH = 4000; PROMPT_SEPARATOR = ", ".
   - config_payload: defaults cho BFL (aspect_ratio, width/height, steps, guidance,
     seed, prompt_upsampling, safety_tolerance, output_format, raw, image_prompt_strength).
   - aspect_ratio lấy từ config_payload['aspect_ratio'] (fallback 1:1).

2) StyleOption
   - thuộc Style, grouped theo group_name.
   - mỗi group ở UI là single-select (chọn 1 option hoặc “mặc định”).
   - thumbnail lưu ở public disk; accessor thumbnail_url -> "/storage/...".

3) Tag
   - tag hiển thị (HOT/MỚI/SALE...) với gradient + icon.
   - Style belongsTo Tag.

4) GeneratedImage
   - status: pending, processing, completed, failed.
   - lưu final_prompt, selected_options (json), user_custom_input, storage_path.
   - **generation_params (json)**: lưu tham số thực tế khi generate (seed/steps/format/ratio/size...).
   - bfl_task_id: id task trả về từ BFL.
   - image_url accessor: pre-signed URL (7 ngày) hoặc URL thường nếu temporaryUrl fail.
   - soft delete enabled.

5) WalletTransaction
   - log mọi cộng/trừ Xu.
   - unique index (source, reference_id) để đảm bảo idempotent.

6) Setting
   - key/value + cache + encrypt nếu is_encrypted.
   - dùng Setting::get / Setting::set, KHÔNG đọc trực tiếp DB ở logic thường.

---------------------------------------------------------------------------
SCHEMA CSDL (TÓM TẮT)
---------------------------------------------------------------------------
users
  - credits (decimal), is_admin, is_active
styles
  - bfl_model_id, base_prompt, config_payload (json),
    image_slots (json), system_images (json), tag_id (fk)
  - openrouter_model_id (legacy, không dùng runtime)
generated_images
  - user_id, style_id, final_prompt, selected_options (json),
    user_custom_input, generation_params (json), storage_path,
    bfl_task_id, status, error_message, credits_used, soft deletes
  - openrouter_id (legacy, không dùng runtime)
wallet_transactions
  - user_id, type(credit/debit), amount, balance_before/after, reason,
    source, reference_id (unique per source)
settings
  - key, value, type, group, is_encrypted
style_options, tags
  - theo chuẩn quản trị UI

---------------------------------------------------------------------------
LUỒNG TẠO ẢNH (USER FLOW)
---------------------------------------------------------------------------
1) User vào /studio/{style:slug}
   - StudioController dùng route model binding theo slug.
   - Style phải is_active, nếu không trả 404.

2) Livewire ImageGenerator
   - Validate:
     + User login + đủ credits
     + Style còn active
     + custom input <= 500 ký tự
     + options thuộc style hiện tại
     + ảnh upload theo image_slots (required + max size 10MB/ảnh)
     + tổng payload ảnh <= 25MB
     + tổng số ảnh <= max_input_images (theo model)
   - Kiểm tra model còn “image-capable” (cache `image_capable_model_ids`).
     Nếu lỗi khi fetch models -> bỏ qua check (log warning).

3) Tạo GeneratedImage status=processing + trừ credits
   - Trừ Xu bằng WalletService (DB::transaction + lockForUpdate).
   - Lưu **generation_params** (seed/steps/guidance/output_format/ratio/size...).

4) Async/Sync:
   - Nếu queue.default != "sync": dispatch GenerateImageJob.
   - Nếu sync: gọi BflService trực tiếp.

5) Job/BFL:
   - BflService -> StorageService -> cập nhật status.
   - Thất bại: mark failed + refund credits.

6) UI polling:
   - Livewire pollImageStatus kiểm tra status mỗi 2s (modal).
   - Timeout 5 phút: fail + refund.

7) Watchdog hệ thống:
   - Kernel scheduler chạy mỗi 5 phút:
     + đánh dấu job processing quá 10 phút là failed
     + hoàn Xu nếu cần.

---------------------------------------------------------------------------
TÍCH HỢP BFL (CHI TIẾT)
---------------------------------------------------------------------------
BflService
- API key:
  + lấy từ Setting::get('bfl_api_key') (đã encrypt) hoặc env BFL_API_KEY.
- Base URL:
  + Setting::get('bfl_base_url') hoặc env BFL_BASE_URL; mặc định https://api.bfl.ai
- HTTP headers: x-key, accept: application/json.
- Submit request:
  + POST /v1/{model} (vd: flux-2-pro, flux-kontext-pro…)
  + trả về id + polling_url.
- Polling:
  + ưu tiên GET polling_url nếu có, fallback /v1/get_result?id=...
  + poll interval ~0.5s, timeout theo config services_custom.bfl.poll_timeout.
- Kết quả:
  + lấy result.sample hoặc result.samples[0] hoặc result.image.
  + nếu là URL -> download về (có SSRF guard) -> convert base64 -> lưu MinIO.
- Input images:
  + chỉ một số model hỗ trợ; giới hạn max_input_images từ config.
- Kích thước:
  + model hỗ trợ aspect_ratio -> gửi aspect_ratio.
  + model hỗ trợ width/height -> map ratio -> kích thước (clamp min/max,
    làm tròn theo dimension_multiple).
  + nếu UI chọn “Nhập kích thước” -> ưu tiên width/height user nhập.
- Payload chỉ gửi các tham số model hỗ trợ (theo capabilities trong config).

ModelManager
- cache list: bfl_models (1h).
- group theo provider (chủ yếu Black Forest Labs).
- dùng trong Admin UI để hiển thị model list.

---------------------------------------------------------------------------
UI/UX – TÙY CHỈNH NÂNG CAO
---------------------------------------------------------------------------
- Có 2 chế độ kích thước:
  + “Theo dáng ảnh” (ratio)
  + “Nhập kích thước” (width/height)
- Ở phần ratio: hiển thị kích thước gợi ý (từ config ratio_dimensions).
- Các tuỳ chọn nâng cao được Việt hoá dễ hiểu cho người dùng phổ thông.
- Tooltip dùng icon “?” để giải thích ngắn gọn.
- Select2:
  + app.blade.php auto init select2.
  + Với Livewire, select “Loại file ảnh” dùng wire:ignore + Alpine entangle
    để tránh bị reset về select mặc định sau khi update.

---------------------------------------------------------------------------
WALLET / CREDITS (QUY ƯỚC BẮT BUỘC)
---------------------------------------------------------------------------
- Tuyệt đối KHÔNG update credits trực tiếp ở User model.
- Luôn dùng WalletService:
  + deductCredits() / addCredits() / refundCredits().
- Tất cả thay đổi Xu phải có WalletTransaction log.
- Idempotency:
  + wallet_transactions có unique index (source, reference_id).

---------------------------------------------------------------------------
LƯU TRỮ (STORAGE)
---------------------------------------------------------------------------
- MinIO disk cấu hình trong config/filesystems.php.
- StorageService:
  + lưu ảnh base64 vào: generated-images/user-{id}/YYYY/MM/filename.ext
  + size max 20MB/ảnh.
- GeneratedImage::image_url:
  + temporaryUrl (expiry 7 ngày), fallback url() nếu temporaryUrl fail.
- StyleOption thumbnails:
  + lưu public disk; cần storage:link để truy cập /storage.

---------------------------------------------------------------------------
SETTINGS + CACHE
---------------------------------------------------------------------------
- Settings lưu trong DB (table settings), cache 1 giờ.
- API key luôn lưu is_encrypted = true.
- Khi cập nhật BFL:
  + clear caches: bfl_models, image_capable_model_ids.

---------------------------------------------------------------------------
BẢO MẬT / AUTH
---------------------------------------------------------------------------
- EnsureUserIsActive middleware:
  + nằm trong web group, auto logout user bị ban.
  + alias "active" dùng cho API.
  + trả JSON 403 cho API requests.
- AdminMiddleware:
  + chặn non-admin.
  + log audit khi truy cập admin.
- HistoryController:
  + bắt buộc ownership để download/delete ảnh.
- BflService:
  + SSRF protection khi tải ảnh từ URL (chặn localhost/private IP).

---------------------------------------------------------------------------
HÀNG ĐỢI + SCHEDULER
---------------------------------------------------------------------------
- GenerateImageJob:
  + tries = 2, timeout = 180s, backoff = [30, 60].
  + fail -> mark failed + refund credits.
- Kernel schedule:
  + images:cleanup chạy 03:00 hàng ngày.
  + watchdog xử lý processing > 10 phút, refund nếu cần.

---------------------------------------------------------------------------
SEEDERS (DEV/LOCAL)
---------------------------------------------------------------------------
- AdminUserSeeder:
  + tạo admin mặc định `admin@ezshot.ai` với mật khẩu random.
  + chỉ tạo nếu chưa tồn tại.
- SettingsSeeder:
  + tạo settings mặc định (site_name, default_credits, bfl_base_url…).
  + API key chỉ là placeholder (cấu hình thật qua Admin hoặc .env).

---------------------------------------------------------------------------
SỰ CỐ THƯỜNG GẶP (GỢI Ý XỬ LÝ)
---------------------------------------------------------------------------
- Lỗi `Target class [Livewire\Mechanisms\ComponentRegistry] does not exist`:
  + Đã alias trong AppServiceProvider -> ComponentRegistryStub.
- Lỗi thiếu cột `generation_params`:
  + chạy migration 2026_01_27_170500_add_generation_params_to_generated_images_table.php
- Ảnh “Đang xử lý” mãi:
  + kiểm tra queue worker đang chạy, scheduler watchdog có hoạt động,
    và log ở storage/logs/laravel.log.
- Lỗi BFL key/credits/timeout:
  + kiểm tra setting bfl_api_key, bfl_base_url, và credits trên BFL.

---------------------------------------------------------------------------
LEGACY / TƯƠNG THÍCH NGƯỢC (KHÔNG DÙNG RUNTIME)
---------------------------------------------------------------------------
- openrouter_model_id, openrouter_id vẫn còn trong DB để migration/compat.
- OpenRouterService + app/Services/ImageGeneration/* chỉ là legacy, không dùng.
- openrouter.txt, debug_models.json, ý tưởng.txt: tài liệu cũ, không dùng runtime.
- Code hiện có fallback: nếu bfl_model_id trống sẽ dùng openrouter_model_id
  để tránh lỗi dữ liệu cũ (không còn gọi OpenRouter).

---------------------------------------------------------------------------
KHI THÊM TÍNH NĂNG MỚI (CHECKLIST)
---------------------------------------------------------------------------
1) BFL model mới:
   - thêm vào config/services_custom.php (models + capabilities).
2) Setting mới:
   - thêm record trong settings + UI Admin + Setting::set.
3) Field mới cho Style/GeneratedImage:
   - cập nhật migration + casts + form admin + logic Livewire.
4) API mới:
   - bảo vệ bằng auth/sanctum hoặc internal secret.
   - log audit nếu liên quan tài chính.
5) Credits:
   - chỉ dùng WalletService, không chỉnh trực tiếp User->credits.
