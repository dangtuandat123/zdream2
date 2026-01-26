Findings

High

H1 — Async image generation can stall without server‑side watchdog
Part: 4 — Integration & Sync
Location: ImageGenerator.php (line 453) Kernel.php (line 16)
Short: Timeout/refund logic only runs during client polling.
Detail: pollImageStatus is the only place that marks long‑running jobs as failed/refunded. If the user closes the tab or the worker is down, processing rows stay stuck and credits remain deducted. Scheduled cleanup doesn’t touch processing rows.
Steps: 1) Set QUEUE_CONNECTION to async and stop the worker. 2) Generate an image and close the tab. 3) Wait >5 minutes and check DB/credits.
Expected: Job auto‑fails and credits refund server‑side.
Actual: Record stays processing, credits stay deducted.
Fix: Add a scheduled watchdog to fail/refund old processing jobs; add queue monitoring/alerts.
Priority: High

H2 — Image uploads allowed for models without image‑input capability
Part: 3 — Image Gen & OpenRouter
Location: ImageGenerator.php (line 251) BaseAdapter.php (line 201)
Short: Uploads are accepted and injected into the payload without capability checks.
Detail: image_slots drive the UI, but there is no guard that the selected model supports image input; input images are always appended. Text‑only models will error after credits are deducted.
Steps: 1) Configure a style with image slots on a text‑only model (e.g., DALL‑E). 2) Upload images and generate.
Expected: UI/validation blocks upload or rejects before charging.
Actual: Request includes images; generation fails.
Fix: Enforce supports_image_input in admin config and generation validation; hide slots or block with a clear message.
Priority: High

H3 — Cleanup can delete valid files or skip deletions when storage_path is a URL
Part: 4 — Integration & Sync
Location: CleanupOrphanImages.php (line 68) CleanupOrphanImages.php (line 138)
Short: Cleanup assumes storage_path is a relative object key.
Detail: URL‑based paths are deleted using the full URL (no‑op) and orphan detection compares file keys to URLs, so valid files can be deleted as “orphan” or never deleted.
Steps: 1) Save a GeneratedImage with a full URL in storage_path. 2) Run images:cleanup.
Expected: URL normalized to object key before delete/compare.
Actual: Incorrect deletion or missed cleanup.
Fix: Normalize URL to a relative object key for delete and orphan checks.
Priority: High

Medium

M1 — Style update blocked when OpenRouter /models is down or incomplete
Part: 2 — API & Backend
Location: StyleController.php (line 82) StyleController.php (line 217) OpenRouterService.php (line 160)
Short: Validation requires model ID to exist in fetched list even during outages.
Detail: fetchImageModels falls back to a limited list on API failure. Styles using non‑fallback models fail validation even if already in use.
Steps: 1) Make /models unreachable. 2) Edit a style using a non‑fallback model. 3) Save.
Expected: Allow existing model IDs or warn when list is stale.
Actual: Validation error blocks save.
Fix: Use last‑known‑good cache or bypass validation when API fails.
Priority: Medium

M2 — image_capable_model_ids cache not cleared on settings update
Part: 3 — Image Gen & OpenRouter
Location: SettingsController.php (line 95) ImageGenerator.php (line 251)
Short: Model capability validation can stay stale after API key/base URL changes.
Detail: Settings update clears OpenRouter caches but not image_capable_model_ids, so generation validation may be wrong for up to 1 hour.
Steps: 1) Change API key/base URL. 2) Generate with a newly supported model.
Expected: Validation uses fresh model list.
Actual: Old list persists until TTL expires.
Fix: Clear image_capable_model_ids when $refreshModels is true.
Priority: Medium

M3 — Upload size validation ignores base64 expansion and system images
Part: 3 — Image Gen & OpenRouter
Location: ImageGenerator.php (line 613) OpenRouterService.php (line 408)
Short: Raw size check doesn’t reflect actual payload size.
Detail: Base64 adds ~33% overhead and system images are appended later without total‑size validation, so provider payload limits can be exceeded.
Steps: 1) Upload images near 25MB and configure multiple system images. 2) Generate.
Expected: Pre‑validation on encoded payload size.
Actual: Oversized payload sent; provider rejects.
Fix: Include base64 expansion and system images in size checks; lower raw limits.
Priority: Medium

M4 — Storage privacy mismatch (public MinIO vs “7‑day link” UX)
Part: 4 — Integration & Sync
Location: filesystems.php (line 69) image-generator.blade.php (line 492)
Short: Disk is public while UI implies time‑limited access.
Detail: minio is configured as public, but UI says share links expire in 7 days, which may be false if the bucket is public.
Steps: 1) Generate image. 2) Access direct storage URL after 7 days.
Expected: Access only via pre‑signed URLs.
Actual: Direct URL may remain accessible.
Fix: Make bucket private and use temporaryUrl, or update UX copy.
Priority: Medium

M5 — Custom prompt can be stale on Generate click
Part: 1 — Logic & UX/UI
Location: image-generator.blade.php (line 194)
Short: wire:model.blur may not sync latest input.
Detail: Clicking Generate can fire before blur updates customInput, so the last keystrokes are dropped.
Steps: 1) Type prompt. 2) Immediately click Generate.
Expected: Latest text used.
Actual: Previous value used.
Fix: Use wire:model.defer with submit or wire:model.live.
Priority: Medium

M6 — Credits displayed as integers, mismatching fractional balances
Part: 1 — Logic & UX/UI
Location: app.blade.php (line 133) index.blade.php (line 15) image-generator.blade.php (line 349)
Short: UI rounds credits while backend supports decimals.
Detail: Credits can be fractional (e.g., VietQR amount not divisible by 1000); UI rounds to 0 decimals, misleading balances and affordability.
Steps: 1) Add 1.5 credits. 2) Check header/wallet balance.
Expected: Accurate decimal display or integer‑only credits.
Actual: Rounded display (e.g., 2).
Fix: Display decimals consistently or enforce integer credits.
Priority: Medium

M7 — API inactive‑user check returns HTML redirect instead of JSON
Part: 2 — API & Backend
Location: api.php (line 18) EnsureUserIsActive.php (line 37)
Short: API clients receive 302 redirect to login.
Detail: EnsureUserIsActive is web‑oriented and redirects; for API calls it should return JSON 401/403.
Steps: 1) Mark user inactive. 2) Call /api/user with a valid token.
Expected: JSON 401/403.
Actual: HTML redirect.
Fix: Add API‑specific middleware or return JSON when expectsJson().
Priority: Medium

M8 — Duplicate image slot keys are allowed
Part: 1 — Logic & UX/UI
Location: StyleController.php (line 109) StyleController.php (line 239)
Short: No uniqueness constraint on image_slots.*.key.
Detail: Duplicate keys cause uploaded images to overwrite each other in uploadedImages, producing incorrect prompt mapping.
Steps: 1) Save a style with duplicate slot keys. 2) Upload two images.
Expected: Validation error or auto‑dedupe.
Actual: One image overwrites the other.
Fix: Enforce unique keys in validation and UI generation.
Priority: Medium

Low

L1 — Raw OpenRouter error details can surface to users
Part: 3 — Image Gen & OpenRouter
Location: OpenRouterService.php (line 496) ImageGenerator.php (line 495)
Short: API error body is stored and shown in UI.
Detail: Provider errors can include raw payload snippets or internal details, which end up displayed to users.
Steps: 1) Trigger an OpenRouter error. 2) Wait for job failure.
Expected: Sanitized, user‑friendly error.
Actual: Raw API error snippet shown.
Fix: Store a clean message; log raw body only.
Priority: Low

L2 — API response shapes inconsistent
Part: 2 — API & Backend
Location: api.php (line 21) InternalApiController.php (line 34)
Short: /api/user returns a bare object while internal APIs return {success: ...}.
Detail: Clients must special‑case responses; schema is inconsistent.
Steps: Call /api/user and /api/internal/wallet/adjust.
Expected: Consistent envelope or documented differences.
Actual: Mixed response shapes.
Fix: Standardize the envelope or document per‑endpoint schema.
Priority: Low

L3 — Mobile menu icon toggling leaves a blank button
Part: 1 — Logic & UX/UI
Location: app.blade.php (line 221) app.blade.php (line 353)
Short: menu-icon-xmark element is missing.
Detail: When menu opens, bars icon is hidden but no X icon appears.
Steps: Open the mobile menu.
Expected: Bars icon switches to X.
Actual: Icon disappears.
Fix: Add the X icon element or adjust toggle logic.
Priority: Low

L4 — Unused artifacts increase maintenance surface
Part: 4 — Integration & Sync
Location: web_debug.php _form.blade.php
Short: Dead files not referenced by routes/views.
Detail: Debug routes file isn’t loaded; legacy admin form isn’t referenced.
Steps: Search for references.
Expected: Remove or wire in intentionally.
Actual: Unused files remain.
Fix: Delete or integrate explicitly.
Priority: Low

L5 — Cannot clear OpenRouter API key via settings form
Part: 2 — API & Backend
Location: SettingsController.php (line 46)
Short: Empty input does not clear existing key.
Detail: filled() guard prevents clearing the key, making key rotation/disablement awkward.
Steps: Submit settings with empty API key.
Expected: Key cleared or explicit “clear key” action.
Actual: Key remains unchanged.
Fix: Add a clear‑key control or allow empty to delete.
Priority: Low

API Inventory

Internal (first‑party)

Method	Endpoint	Purpose	Auth	Required Inputs	Optional Inputs	Output	Notes
GET	/api/user	Current user info	auth:sanctum + active	Token/session	—	{id,name,email,credits}	Bare object, no envelope.
POST	/api/internal/wallet/adjust	Credit/debit user wallet	X-API-Secret	user_id, amount, reason	source, reference_id	{success,transaction_id,new_balance} or error	Throttled throttle:60,1.
POST	/api/internal/payment/callback	VietQR webhook credit	X-API-Secret	user_id, amount, transaction_ref	—	{success,transaction_id,new_balance}	Converts amount/1000; idempotent by source+reference_id.
POST	/livewire/...	Livewire image generation + polling	Session auth	Component state	—	Livewire JSON + HTML diff	Internal Livewire routes.
External

Service	Endpoint	Purpose	Headers/Params	Payload/Response	Notes
OpenRouter	{base}/chat/completions	Image generation	Authorization, HTTP-Referer, X-Title	model, messages, modalities, optional image_config	Timeout 120s, retry 3.
OpenRouter	{base}/models	Model discovery	Same headers	data[] with modalities	Cached 1h.
OpenRouter	{base}/key	Key/balance	Same headers	data	Used in admin dashboard.
VietQR	https://api.vietqr.io/image/{bankId}-{accountNumber}-{template}.jpg	QR code	Query: accountName, addInfo, amount?	Image	Used in wallet page.
Test Gaps

No tests for GenerateImageJob success/failure/refund flows and race conditions.
No tests for OpenRouter adapters (Gemini/Flux/GPT/Generic) payload/response parsing.
No tests for internal API idempotency/authorization and error handling.
No tests for images:cleanup URL vs path normalization and orphan detection.
Open Questions / Assumptions

Are credits intended to be integer‑only? If yes, enforce at DB/API/UI; if no, update UI formatting.
Do you still store any storage_path as full URLs? If not, cleanup risk is lower but still latent.
Are any styles configured with models that don’t accept image input?
Brief summary: 3 High, 8 Medium, 5 Low issues; the biggest risks are async job stalls without server‑side refunds, missing image‑input capability checks, and cleanup path handling for URL storage paths.

If you want me to act on fixes next, pick one:

Fix High‑priority issues (watchdog/refund, model input validation, cleanup normalization).
Align API/UX inconsistencies (stale cache, API responses, credit display, input sync).
Add targeted tests for image generation, OpenRouter parsing, internal APIs, and cleanup.