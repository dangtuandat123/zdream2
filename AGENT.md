# AGENT - zdream.vn2 (ZDream/EZShot AI)

Tài liệu này là “bản đồ tư duy” của dự án để hiểu đúng logic, luồng dữ liệu,
quy ước code và các ràng buộc nghiệp vụ. Khi kiến trúc hoặc luồng chính thay
đổi, phải cập nhật lại file này.

---------------------------------------------------------------------------
TỔNG QUAN HỆ THỐNG
---------------------------------------------------------------------------
- Stack: Laravel 10 + Livewire 4 + Tailwind CSS + Alpine (Livewire 4 đã bundle).
- Build frontend: Vite.
- Tạo ảnh AI: OpenRouter API (đa model, xử lý qua adapter).
- Lưu trữ ảnh: MinIO (S3 compatible) cho ảnh tạo ra; public disk cho thumbnail
  của StyleOption.
- Nạp credits: VietQR (chuyển khoản) -> cộng Xu.
- Auth: Laravel Breeze (web) + Sanctum (API).

---------------------------------------------------------------------------
CẤU TRÚC THƯ MỤC / FILE MAP (NHỮNG NƠI QUAN TRỌNG)
---------------------------------------------------------------------------
app/
  Models/
    User, Style, StyleOption, Tag, GeneratedImage, WalletTransaction, Setting
  Services/
    OpenRouterService (gọi API + xử lý response)
    ModelManager (cache & group model)
    StorageService (lưu base64 -> MinIO)
    WalletService (cộng/trừ/hoàn Xu, log transaction)
    ImageGeneration/ (adapters: Gemini, Flux, GPT, Generic)
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

config/
  services_custom.php (OpenRouter, VietQR, pricing, internal API secret)
  filesystems.php (minio disk)

routes/
  web.php (public + auth + admin)
  api.php (sanctum + internal API)
  web_debug.php (không đăng ký trong RouteServiceProvider)

database/migrations/
  Schema users, styles, style_options, generated_images, wallet_transactions,
  settings, tags, soft deletes, v.v.

resources/views/
  layouts/app.blade.php (global layout + lightbox + select2 init)
  livewire/image-generator.blade.php (UI tạo ảnh)
  admin/* (UI admin)
  home, styles, studio, history, wallet, dashboard

resources/js/app.js
  Không import Alpine thủ công (Livewire 4 đã bundle).

html_thuong/
  Bản prototype tĩnh, không kết nối Laravel.

openrouter.txt / debug_models.json
  Tài liệu/tham khảo, không chạy runtime.

public/build/
  Output Vite (không chỉnh tay).

---------------------------------------------------------------------------
ĐỐI TƯỢNG MIỀN & QUY ƯỚC NGHIỆP VỤ
---------------------------------------------------------------------------
1) Style
   - fields chính: name, slug, thumbnail_url, description, price,
     openrouter_model_id, base_prompt, config_payload, is_active,
     allow_user_custom_prompt, image_slots, system_images, sort_order, tag_id.
   - slug: tự tạo khi create; KHÔNG tự đổi khi update (tránh vỡ URL).
   - buildFinalPrompt(): ghép base_prompt + options + custom input.
   - MAX_PROMPT_LENGTH = 4000; PROMPT_SEPARATOR = ", ".
   - buildOpenRouterPayload(): gửi modalities ['image','text']; image_config
     chỉ dùng cho Gemini.
   - aspect_ratio lấy từ config_payload['aspect_ratio'] (fallback 1:1).

2) StyleOption
   - thuộc Style, grouped theo group_name.
   - mỗi group về UI là single-select (chọn 1 option hoặc “mặc định”).
   - thumbnail lưu ở public disk; accessor thumbnail_url -> "/storage/...".

3) Tag
   - tag hiển thị (HOT/MỚI/SALE...) với gradient + icon.
   - Style belongsTo Tag.

4) GeneratedImage
   - status: pending, processing, completed, failed.
   - lưu final_prompt, selected_options (json), user_custom_input, storage_path.
   - image_url accessor: trả temporaryUrl (MinIO) hoặc URL đầy đủ nếu đã có.
   - soft delete enabled (phục hồi được).

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
  - openrouter_model_id, base_prompt, config_payload (json),
    image_slots (json), system_images (json), tag_id (fk)
style_options
  - style_id (fk), label, group_name, prompt_fragment, icon, thumbnail,
    is_default, sort_order
generated_images
  - user_id, style_id, final_prompt, selected_options (json),
    user_custom_input, storage_path, openrouter_id, status, error_message,
    credits_used, soft deletes
wallet_transactions
  - user_id, type(credit/debit), amount, balance_before/after, reason,
    source, reference_id (unique per source)
settings
  - key, value, type, group, is_encrypted
tags
  - name, color_from, color_to, icon, sort_order, is_active

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
     + ảnh upload theo image_slots (required + max size)
     + tổng payload ảnh <= 25MB
   - Kiểm tra model còn “image-capable” (cache `image_capable_model_ids` từ
     ModelManager). Nếu model không còn hỗ trợ -> báo lỗi cho user.
   - Nếu lỗi khi fetch models -> bỏ qua check (log warning).

3) Tạo GeneratedImage status=processing + trừ credits
   - Trừ Xu bằng WalletService (DB::transaction + lockForUpdate).

4) Async/Sync:
   - Nếu queue.default != "sync": dispatch GenerateImageJob.
   - Nếu sync: gọi OpenRouterService trực tiếp.

5) Job/OpenRouter:
   - OpenRouterService -> StorageService -> cập nhật status.
   - Thất bại: mark failed + refund credits.

6) UI polling:
   - Livewire pollImageStatus kiểm tra status mỗi 2s (modal).
   - Timeout 5 phút: fail + refund.

7) Watchdog hệ thống:
   - Kernel scheduler chạy mỗi 5 phút:
     + đánh dấu job processing quá 10 phút là failed
     + hoàn Xu nếu chưa hoàn.

---------------------------------------------------------------------------
DỌN DẸP ẢNH / DỮ LIỆU (CLEANUP)
---------------------------------------------------------------------------
Artisan command: `images:cleanup`
- Soft delete ảnh failed quá `--failed-days` (mặc định 7 ngày).
- Force delete ảnh đã soft delete quá `--deleted-days` (mặc định 30 ngày),
  đồng thời xóa file trên MinIO.
- Soft delete ảnh completed quá `image_expiry_days` (Setting, mặc định 30).
- Xóa file “orphan” trong MinIO không còn record DB tham chiếu.

---------------------------------------------------------------------------
TÍCH HỢP OPENROUTER (CHI TIẾT)
---------------------------------------------------------------------------
OpenRouterService
- Base URL:
  + lấy từ Setting::get('openrouter_base_url') hoặc config.
  + tự normalize để kết thúc bằng /api/v1.
- HTTP client:
  + retry/backoff khi 429/5xx, timeout phù hợp cho image models.
- fetchImageModels():
  + gọi /models, lọc model có output_modalities chứa 'image'.
  + fallback list nếu API thiếu modalities (config services_custom).
  + cache: openrouter_image_models.
- generateImage():
  + buildFinalPrompt từ Style.
  + chọn adapter (Gemini/Flux/GPT/Generic).
  + gắn input images (img2img) + system_images.
  + gọi /chat/completions.
  + parse response -> base64 image.
- SSRF Protection:
  + chặn localhost/private IP khi tải ảnh từ URL.
  + nếu URL là MinIO endpoint thì đọc trực tiếp Storage (an toàn hơn).

ModelManager
- cache enhanced list: openrouter_models_enhanced (1h).
- group theo provider (Google/OpenAI/Black Forest Labs/Stability/…).
- dùng trong Admin UI để:
  + hiển thị model list
  + validate model khi tạo/sửa Style.

Adapters (app/Services/ImageGeneration)
- ModelAdapterInterface:
  + preparePayload(), parseResponse(), extractTextResponse().
- GeminiAdapter:
  + hỗ trợ image_config (aspect_ratio, image_size).
- FluxAdapter:
  + không hỗ trợ image_config -> append tỉ lệ vào prompt.
- GptImageAdapter:
  + thêm prefix bắt buộc “generate image only”.
- GenericAdapter:
  + fallback cho model không xác định.

---------------------------------------------------------------------------
WALLET / CREDITS (QUY ƯỚC BẮT BUỘC)
---------------------------------------------------------------------------
- Tuyệt đối KHÔNG update credits trực tiếp ở User model.
- Luôn dùng WalletService:
  + deductCredits() / addCredits() / refundCredits().
- Tất cả thay đổi Xu phải có WalletTransaction log.
- Dùng DB::transaction + lockForUpdate để tránh race conditions.
- Idempotency:
  + wallet_transactions có unique index (source, reference_id).
  + internal API cũng kiểm tra trùng trước khi cộng/trừ.

---------------------------------------------------------------------------
INTERNAL API (NỘI BỘ)
---------------------------------------------------------------------------
POST /api/internal/wallet/adjust
POST /api/internal/payment/callback
Yêu cầu:
- Header: X-API-Secret (INTERNAL_API_SECRET)
- Nếu secret trống -> fail-close (Unauthorized)
Callback VietQR:
- amount VND -> credits (1000 VND = 1 Xu).
- xử lý idempotent bằng DB transaction + lockForUpdate.
Rate limit:
- /api/internal/* = throttle:60,1.

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
- Settings lưu trong DB (table settings).
- Setting::get cache 1 giờ.
- Khi cập nhật OpenRouter:
  + clear caches: openrouter_image_models, openrouter_models_enhanced,
    image_capable_model_ids.

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
- OpenRouterService + BaseAdapter:
  + SSRF protection khi tải ảnh từ URL.

---------------------------------------------------------------------------
FRONTEND (QUY ƯỚC GIAO DIỆN)
---------------------------------------------------------------------------
- layouts/app.blade.php:
  + lightbox toàn cục (download/delete).
  + jQuery + Select2 từ CDN.
  + auto init Select2 cho tất cả <select>, re-init sau Livewire update.
- Livewire 4 đã bundle Alpine:
  + không import Alpine trong resources/js/app.js.
- CSS chính: resources/css/app.css (glassmorphism).
- Placeholder ảnh: public/images/placeholder.svg.

---------------------------------------------------------------------------
HÀNG ĐỢI + SCHEDULER
---------------------------------------------------------------------------
- GenerateImageJob:
  + tries = 2, timeout = 180s, backoff = [30, 60].
  + fail -> mark failed + refund credits.
- Kernel schedule:
  + images:cleanup chạy 03:00 hằng ngày.
  + watchdog xử lý processing > 10 phút, refund nếu cần.

---------------------------------------------------------------------------
SEEDERS (DEV/LOCAL)
---------------------------------------------------------------------------
- AdminUserSeeder:
  + tạo admin mặc định `admin@ezshot.ai` với mật khẩu random.
  + chỉ tạo nếu chưa tồn tại.
- SettingsSeeder:
  + tạo settings mặc định (site_name, default_credits, openrouter_base_url…).
  + API key chỉ là placeholder (cấu hình thật qua Admin hoặc .env).

---------------------------------------------------------------------------
MỤC CŨ / DI SẢN (CẦN BIẾT)
---------------------------------------------------------------------------
- resources/views/admin/styles/_form.blade.php: có vẻ legacy, hiện không dùng.
- routes/web_debug.php: không đăng ký route, chỉ có debug route trong web.php
  (chạy khi APP_ENV=local).
- html_thuong/: prototype tĩnh, không ảnh hưởng runtime.

---------------------------------------------------------------------------
KHI THÊM TÍNH NĂNG MỚI (CHECKLIST)
---------------------------------------------------------------------------
1) OpenRouter model mới:
   - viết adapter (nếu cần) + register trong ModelAdapterFactory.
2) Setting mới:
   - thêm record trong settings + UI Admin + Setting::set.
3) Field mới cho Style/GeneratedImage:
   - cập nhật migration + casts + form admin + logic Livewire.
4) API mới:
   - bảo vệ bằng auth/sanctum hoặc internal secret.
   - log audit nếu liên quan tài chính.
5) Credits:
   - chỉ dùng WalletService, không chỉnh trực tiếp User->credits.
