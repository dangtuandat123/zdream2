# Báo cáo lỗi (Code Audit)

## 1. Bảng Tổng Hợp Lỗi (Critical Issues)
| Vị trí (File/Line) | Loại lỗi (Logic/API/UI/Sync) | Mô tả chi tiết | Mức độ (Critical/High/Medium) | Đề xuất sửa (Code cụ thể) |
| :--- | :--- | :--- | :--- | :--- |
| app/Http/Controllers/Api/InternalApiController.php:36 | API | Nếu internal_api_secret rỗng/không set, so sánh !== sẽ cho phép request không có header đi qua (fail-open). Ngoài ra so sánh thường không chống timing attack. | Critical | ```php
$secret = config('services_custom.internal_api_secret');
if (empty($secret) || !hash_equals($secret, (string) $apiSecret)) {
    return response()->json(['error' => 'Unauthorized'], 401);
}
```
| app/Services/OpenRouterService.php:71 | Logic/API | checkBalance() đang dùng $style/$finalPrompt không tồn tại, parse ảnh từ /auth/key và có return dư -> runtime error và kết quả sai. | High | Viết lại hàm chỉ đọc credit_balance/usage/rate_limit, bỏ toàn bộ parse ảnh và biến không tồn tại. |
| app/Services/OpenRouterService.php:519 | API | extractImageFromResponse() trả thẳng image_url.url; nếu là URL HTTP thì StorageService::saveBase64Image sẽ fail (đòi base64). | High | ```php
if (filter_var($url, FILTER_VALIDATE_URL)) {
    return $this->downloadImageAsBase64($url);
}
``` |
| app/Services/StorageService.php:41 | Logic | base64_decode không strict + không giới hạn kích thước -> lưu dữ liệu rác/không kiểm soát dung lượng. | High | ```php
$decoded = base64_decode($imageData, true);
if ($decoded === false || strlen($decoded) > $this->maxBytes) { return error; }
```
| app/Services/OpenRouterService.php:594 | API | downloadImageAsBase64() tải mọi URL từ system_images -> nguy cơ SSRF nếu URL bị lợi dụng. | High | Allowlist host (vd chỉ MinIO) hoặc chặn IP private; hoặc lưu base64 từ lúc upload. |
| app/Services/OpenRouterService.php:37 | API | Không có retry/backoff/connect-timeout -> lỗi 429/5xx gây fail ngay. | Medium | ```php
return Http::...->connectTimeout(10)->retry(2, 500);
``` |
| resources/views/livewire/image-generator.blade.php:129 | UI/Sync | Check Gemini case-sensitive, không đồng bộ với backend (strtolower) -> UI hiển thị sai image size. | Medium | Dùng $supportsImageConfig hoặc str_contains(strtolower(...),'gemini'). |
| app/Livewire/ImageGenerator.php:233 | Logic/UX | UI báo "credits đã hoàn" nhưng refund có thể thất bại (đang swallow). | Medium | Trả về trạng thái refund và cập nhật message cho phù hợp. |
| app/Http/Controllers/Admin/StyleController.php:69 | UI/Sync | image_slots.*.key không ràng buộc pattern -> key có dấu '.' sẽ phá Livewire binding uploadedImages.{key}. | Medium | Thêm rule regex: /^[a-zA-Z0-9_-]+$/ và normalize key. |

## 2. Review Chuyên Sâu OpenRouter & AI
- Trạng thái hiện tại:
  - API key/base URL lấy từ Settings; gọi POST /chat/completions với modalities, messages, image_config (Gemini).
  - Có hỗ trợ img2img (base64 từ upload) và system images (download URL -> base64).
  - Timeout 120s + set_time_limit(180); log payload đã redact.
  - Flow đang synchronous trong Livewire: trừ credits -> gọi OpenRouter -> lưu MinIO -> trả kết quả.

- Lỗ hổng tìm thấy:
  - checkBalance() sai logic và có biến undefined (High).
  - Không retry/backoff, dễ fail với 429/5xx hoặc lỗi mạng (Medium).
  - Parse ảnh chưa normalize URL->base64, dễ fail với model trả URL (High).
  - Không kiểm soát kích thước base64 đầu ra; nguy cơ memory/disk bloat (High).
  - system_images download URL có thể SSRF nếu bị lợi dụng (High).
  - Log response->body() có thể rất lớn/nhạy cảm (Medium).
  - Flow synchronous: request block PHP worker khi model chậm (Performance/UX).

- Code refactor (mẫu thay thế trọng tâm):
```php
protected function client(): PendingRequest
{
    return Http::withHeaders([
        'Authorization' => 'Bearer ' . $this->apiKey,
        'Content-Type' => 'application/json',
        'HTTP-Referer' => config('app.url'),
        'X-Title' => config('app.name'),
    ])->acceptJson()
      ->timeout($this->timeout)
      ->connectTimeout(10)
      ->retry(2, 500);
}

public function checkBalance(): array
{
    $response = $this->client()->get($this->baseUrl . '/auth/key');

    if (!$response->successful()) {
        return ['error' => 'Failed to check balance', 'status' => $response->status()];
    }

    $data = $response->json()['data'] ?? [];
    return [
        'balance' => (float) ($data['credit_balance'] ?? 0),
        'usage' => $data['usage'] ?? [],
        'rate_limit' => $data['rate_limit'] ?? [],
    ];
}

protected function extractImageFromResponse(array $data): ?string
{
    $candidates = [
        $data['choices'][0]['message']['images'][0]['image_url']['url'] ?? null,
        $data['choices'][0]['message']['images'][0]['url'] ?? null,
        $data['choices'][0]['message']['content'][0]['image_url']['url'] ?? null,
        $data['choices'][0]['message']['content'] ?? null,
    ];

    foreach ($candidates as $value) {
        if (!$value) { continue; }
        if (str_starts_with($value, 'data:image/')) { return $value; }
        if (filter_var($value, FILTER_VALIDATE_URL)) { return $this->downloadImageAsBase64($value); }
        if (is_string($value) && $this->isBase64Image($value)) { return $value; }
    }

    return null;
}
```

## 3. Checklist Production
- [ ] Security (internal API fail-open, SSRF từ system_images, log payload lớn)
- [ ] Performance (synchronous generation, download system image, không retry)
- [ ] UX (image size selector không đồng bộ, thông báo hoàn tiền có thể sai)

Ghi chú/giả định:
- Giả định một số model trả URL ảnh thay vì data:image/...; nếu không, vẫn nên normalize để an toàn.
- Giả định INTERNAL_API_SECRET chưa được bắt buộc ở môi trường production.
