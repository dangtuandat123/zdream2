High Issues

H1 — Image uploads allowed for models without image‑input capability. Part: 3. Location: ImageGenerator.php (line 313), OpenRouterService.php (line 245). Short: uploads are accepted and injected even when a model doesn’t support image input. Detail: styles can be configured with image slots on text‑only input models, causing OpenRouter errors after credits are deducted. Steps: (1) Create a style with an image‑output model that lacks image input (e.g., DALL‑E). (2) Add image slots and upload images. (3) Generate. Expected: validation blocks or warns before charging. Actual: payload includes images and request fails post‑charge (refund depends on async flow). Fix: enforce supports_image_input in admin config and runtime validation; hide/disable slots when unsupported. Priority: High.
H2 — Cleanup job mis‑handles URL storage_path. Part: 4. Location: CleanupOrphanImages.php (line 68), CleanupOrphanImages.php (line 138). Short: cleanup assumes storage_path is a relative object key. Detail: URL paths are deleted as full URLs and orphan detection compares keys to URLs, causing missed deletes or false orphan removal. Steps: (1) Store a GeneratedImage with a full URL in storage_path. (2) Run php artisan images:cleanup. Expected: URL normalized to object key before delete/compare. Actual: deletes no‑op and orphan detection is incorrect. Fix: normalize URLs to object keys for delete and orphan checks. Priority: High.
Medium Issues

M1 — Style save blocked when OpenRouter /models is down. Part: 2. Location: StyleController.php (line 82), OpenRouterService.php (line 171). Short: validation requires model ID to be in the current fetch list even during outages. Detail: when /models fails, fallback list is limited; styles using valid models outside fallback cannot be saved. Steps: (1) Make /models unreachable or API key invalid. (2) Edit a style using a non‑fallback model. (3) Save. Expected: allow existing model IDs or use last‑known‑good cache. Actual: validation error blocks save. Fix: retain last‑known‑good list and/or bypass validation on API failure. Priority: Medium.
M2 — image_capable_model_ids cache not cleared on settings update. Part: 3. Location: SettingsController.php (line 95), ImageGenerator.php (line 251). Short: model capability validation stays stale after API key/base URL changes. Detail: settings update clears OpenRouter caches but not image_capable_model_ids, so generation validation can reject valid models for up to 1 hour. Steps: (1) Update OpenRouter API key/base URL. (2) Generate with newly supported model. Expected: immediate validation against updated list. Actual: stale cache causes false rejection. Fix: clear image_capable_model_ids when $refreshModels is true. Priority: Medium.
M3 — Payload size validation ignores base64 expansion and system images. Part: 3. Location: ImageGenerator.php (line 614), OpenRouterService.php (line 408). Short: raw size checks do not reflect real request size. Detail: base64 inflates size ~33% and system images are appended later without size validation, causing provider rejections after charging. Steps: (1) Upload images near 25MB and configure system images. (2) Generate. Expected: validation fails before charging. Actual: request sent and provider rejects. Fix: estimate encoded payload size (including system images) and cap earlier. Priority: Medium.
M4 — Storage privacy mismatch vs “7‑day link” UX. Part: 4. Location: filesystems.php (line 59), image-generator.blade.php (line 489). Short: MinIO disk is public but UI claims 7‑day expiry. Detail: public bucket URLs may remain accessible beyond 7 days, contradicting UX copy. Steps: (1) Generate image. (2) Access direct storage URL after 7 days. Expected: access expires or requires pre‑signed URL. Actual: URL may still work. Fix: make bucket private and rely on pre‑signed URLs, or update UX copy. Priority: Medium.
M5 — Credits shown as integers despite decimal support. Part: 1. Location: app.blade.php (line 133). Short: UI rounds credits while backend stores decimals. Detail: fractional credits (e.g., 1.5 from 1500 VND) are displayed as whole numbers, misleading balance/affordability. Steps: (1) Credit 1500 VND via callback. (2) Check header/wallet/generator. Expected: 1.5 shown. Actual: rounded to 1 or 2. Fix: display decimals consistently or enforce integer‑only credits at API/DB/UI. Priority: Medium.
M6 — is_featured/is_new flags are dead. Part: 1/4. Location: 2026_01_26_000001_add_tag_fields_to_styles_table.php (line 18), Style.php (line 35), StyleController.php (line 146). Short: columns exist but model/controller/view don’t wire them. Detail: fields aren’t fillable/cast and aren’t saved in create/update payloads; no current UI surfaces them. Steps: (1) Attempt to set flags. (2) Check DB/UI. Expected: flags saved and drive badges. Actual: flags never change. Fix: add to model, persist in controller, and expose in admin UI (or remove columns). Priority: Medium.
Low Issues

L1 — Raw OpenRouter error details can surface to users. Part: 3. Location: OpenRouterService.php (line 496), GenerateImageJob.php (line 110). Short: provider error body is stored and shown. Detail: API error bodies can include sensitive or noisy details that appear in UI. Steps: (1) Trigger OpenRouter error. (2) Wait for failure. Expected: sanitized user‑friendly error. Actual: raw error snippet displayed. Fix: store a clean message; log raw body only. Priority: Low.
L2 — API response envelopes inconsistent. Part: 2. Location: api.php (line 21), InternalApiController.php (line 95). Short: /api/user returns a bare object while internal APIs wrap responses. Detail: clients must special‑case response shapes. Steps: call /api/user and /api/internal/wallet/adjust. Expected: consistent envelope. Actual: mixed formats. Fix: standardize envelopes or document per‑endpoint schema. Priority: Low.
L3 — Mobile menu icon toggling hides icon. Part: 1. Location: app.blade.php (line 220), app.blade.php (line 354). Short: menu-icon-xmark is referenced but not rendered. Detail: when menu opens, bars icon hides and no X icon appears. Steps: open mobile menu. Expected: bars icon switches to X. Actual: icon disappears. Fix: add the X icon element or adjust toggle logic. Priority: Low.
L4 — Unused artifacts remain in repo. Part: 4. Location: web_debug.php, _form.blade.php. Short: files are not referenced by route providers or views. Detail: dead code paths can drift and confuse maintenance. Steps: search for references. Expected: remove or wire intentionally. Actual: unused files remain. Fix: delete or integrate explicitly. Priority: Low.
L5 — Cannot clear OpenRouter API key via settings form. Part: 2. Location: SettingsController.php (line 45). Short: filled() guard prevents clearing. Detail: empty submissions leave the previous key intact. Steps: submit settings with empty key. Expected: key cleared or explicit clear action. Actual: key stays unchanged. Fix: add a clear‑key action or allow empty to delete. Priority: Low.
API Inventory
Internal

Method	Endpoint	Purpose	Auth	Required Inputs	Optional Inputs	Output	Notes
GET	/api/user	Current user info	auth:sanctum + active	token/session	—	{id,name,email,credits}	Bare object (no envelope).
POST	/api/internal/wallet/adjust	Credit/debit user wallet	X-API-Secret	user_id, amount, reason	source, reference_id	{success,transaction_id,new_balance} or {success:false,error}	Throttled throttle:60,1.
POST	/api/internal/payment/callback	VietQR webhook credit	X-API-Secret	user_id, amount, transaction_ref	—	{success,transaction_id,new_balance}	Idempotent by source+reference_id, credits = amount/1000.
POST	/livewire/*	Image generation + polling	Session auth	Livewire payload	—	Livewire JSON/HTML	Generated by Livewire, not explicitly routed.
External

Service	Endpoint	Purpose	Headers/Params	Payload/Response	Notes
OpenRouter	{base}/chat/completions	Image generation	Authorization, HTTP-Referer, X-Title	model, messages, modalities, optional image_config; response choices[].message.images[]	Timeout 120s, retries on 429/5xx.
OpenRouter	{base}/models	Model discovery	same headers	data[] models	Cached 1h.
OpenRouter	{base}/key	Balance/credits	same headers	data	Used in admin dashboard.
VietQR	https://api.vietqr.io/image/{bankId}-{accountNumber}-{template}.jpg	QR code	Query: accountName, addInfo, amount?	Image	Used in wallet page.
Questions / Assumptions

Are credits intended to be integer‑only or can they be fractional?
Do any generated_images.storage_path values currently store full URLs in production?
Are any styles configured with image slots but models lacking input_modalities: image?
Is the MinIO bucket intended to be private (pre‑signed only) or public?
Should is_featured/is_new be implemented or removed?
Test Gaps

No tests for GenerateImageJob success/failure/refund flows or watchdog/race conditions.
No tests for OpenRouter adapters (payload prep + response parsing).
No tests for internal API idempotency/authorization error paths.
No tests for images:cleanup URL normalization and orphan detection.