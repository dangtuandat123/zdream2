# Tài liệu dự án (Blueprint tổng quan)

> Mục tiêu: mô tả dự án một cách **trung lập công nghệ**, đủ chi tiết để bất kỳ ai cũng có thể xây dựng lại hệ thống tương đương.

---

## 1) Tổng quan dự án

Nền tảng tạo ảnh AI cho người dùng phổ thông. Người dùng chọn **Style** (mẫu phong cách), chọn vài tùy chọn đơn giản, hệ thống tự ghép prompt và gọi API nhà cung cấp AI để tạo ảnh. Hệ thống có **ví nội bộ (credits/xu)**, lịch sử giao dịch, lịch sử ảnh và khu vực quản trị để cấu hình Style, model AI, giá, API key, v.v.

### Công nghệ/tích hợp đang dùng và vai trò
- **Backend (Laravel 12)**: xử lý API, xác thực, business logic, job queue, giao tiếp DB.
- **Frontend (React CSR)**: giao diện SPA, tương tác realtime (polling), form/UX.
- **MinIO (S3-compatible)**: lưu trữ ảnh, trả ảnh bằng pre‑signed URL.
- **BFL (bfl.ai / FLUX)**: nhà cung cấp mô hình AI tạo ảnh.
- **VietQR**: tạo QR chuyển khoản và nhận callback nạp tiền tự động.

### Giá trị cốt lõi
- **Zero‑prompt**: không yêu cầu người dùng hiểu prompt kỹ thuật.
- **Trải nghiệm nhanh**: hiển thị trạng thái xử lý, phản hồi rõ ràng.
- **Kinh tế minh bạch**: credits bị trừ/hoàn lại có log và idempotency.
- **Quản trị linh hoạt**: admin cấu hình Style, tag, model, thông số nâng cao.

---

## 2) Thuật ngữ chính

- **Style**: một công thức tạo ảnh gồm prompt nền, model AI, giá và cấu hình UI.
- **Option**: lựa chọn bổ sung theo nhóm (vd: lighting, background).
- **Generated Image**: ảnh đã tạo kèm metadata (prompt, tham số, trạng thái).
- **Credits/Xu**: đơn vị tiền nội bộ để tính phí tạo ảnh.
- **Tag/Chủ đề**: nhãn để lọc/hiển thị Style.
- **Image Slots**: các ô upload ảnh tham chiếu có thể bắt buộc.

---

## 3) Luồng nghiệp vụ chính

### 3.1. Duyệt Style → Studio → Tạo ảnh
1) Người dùng mở trang danh mục Style.
2) Chọn một Style để vào Studio.
3) Tại Studio:
   - Chọn **options** theo nhóm.
   - (Tuỳ Style) nhập mô tả bổ sung.
   - (Tuỳ model) chọn tỉ lệ khung hình hoặc kích thước tuỳ chỉnh.
   - (Tuỳ model) bật các tuỳ chọn nâng cao (seed, steps, guidance, format…).
   - (Tuỳ model) tải ảnh tham chiếu theo các **image slots**.
4) Nhấn **Tạo ảnh**:
   - Hệ thống kiểm tra credits và validate dữ liệu.
   - Trừ credits và tạo bản ghi `generated_images` trạng thái `processing`.
   - Gửi job nền gọi API AI, lưu ảnh vào storage.
5) UI polling:
   - Thành công: hiển thị ảnh + lưu lịch sử.
   - Thất bại/timeout: hoàn credits và hiển thị lỗi rõ ràng.

### 3.2. Ví & nạp tiền
- Người dùng xem **số dư** và lịch sử giao dịch gần nhất.
- Hệ thống hỗ trợ **nạp tiền qua QR/chuyển khoản** (callback nội bộ).
- Quy đổi VND ↔ credits dựa trên **cấu hình trong Settings**.

### 3.3. Lịch sử ảnh
- Danh sách ảnh đã tạo, lọc theo trạng thái/style.
- Cho phép tải ảnh và xóa ảnh.

### 3.4. Quản trị
- Quản lý Style, Option, Tag, người dùng, giao dịch, cài đặt hệ thống.

---

## 4) Màn hình & chức năng người dùng (UI)

> Mô tả chức năng, không ràng buộc vào công nghệ UI cụ thể.

### 4.1. Trang chủ
- Hiển thị bộ sưu tập Style nổi bật.
- Sắp xếp theo mức độ phổ biến (dựa trên số lượng ảnh đã tạo).

### 4.2. Danh mục Style
- Tìm kiếm theo tên.
- Lọc theo giá (miễn phí / thấp / trung / cao).
- Lọc theo **Tag/Chủ đề**.
- Sắp xếp (mới nhất, phổ biến, giá tăng/giảm).
- Phân trang, giữ tham số lọc trên URL.

### 4.3. Studio (tạo ảnh)
- Thông tin Style, mô tả ngắn, giá.
- Khối lựa chọn **options** theo nhóm (single‑select).
- Ô nhập mô tả bổ sung (nếu Style cho phép).
- Tuỳ chọn **dáng ảnh** (tỉ lệ) hoặc **kích thước tùy chỉnh**.
- Tuỳ chọn nâng cao:
  - seed, steps, guidance, safety_tolerance, output_format, prompt_upsampling, raw, image_prompt_strength…
- Khu vực upload ảnh tham chiếu theo các slot.
- Trạng thái tạo ảnh: đang xử lý, hoàn thành, lỗi.
- Hiển thị ảnh kết quả; cập nhật lịch sử của Style.

### 4.4. Ví (Wallet)
- Hiển thị số dư hiện tại.
- QR nạp tiền + thông tin chuyển khoản để copy.
- Lịch sử giao dịch gần nhất.

### 4.5. Lịch sử ảnh
- Lọc theo trạng thái & Style.
- Xem thumbnail, thời gian, trạng thái.
- Tải ảnh (download) & xóa ảnh.

### 4.6. Tài khoản
- Đăng ký, đăng nhập, quên mật khẩu, xác minh email.
- Cập nhật thông tin, đổi mật khẩu, xoá tài khoản.

---

## 5) Quản trị (Admin)

### 5.1. Quản lý Style
- Tạo/sửa: tên, slug, mô tả, giá, thumbnail, trạng thái.
- Chọn **model AI** và prompt nền.
- Cấu hình `config_payload` cho thông số mặc định.
- Cấu hình **image slots** (key, label, required, description).
- Cấu hình **system images** (URL, label, description).
- Gắn **Tag/Chủ đề**, sắp xếp hiển thị.

### 5.2. Quản lý Option (theo Style)
- Tạo/sửa option: label, group_name, prompt_fragment.
- Thiết lập option mặc định, thứ tự hiển thị.
- (Tuỳ chọn) icon/thumbnail.

### 5.3. Quản lý Tag
- Tạo/sửa tag: tên, màu gradient, icon, thứ tự, trạng thái.

### 5.4. Quản lý User & Giao dịch
- Danh sách user, xem chi tiết, chỉnh sửa trạng thái.
- Điều chỉnh credits (cộng/trừ).
- Xem lịch sử giao dịch toàn hệ thống.

### 5.5. Quản lý Ảnh
- Danh sách ảnh, xem chi tiết, xoá ảnh theo user.

### 5.6. Settings
- API key & base URL của AI provider (mã hoá).
- Cấu hình quy đổi VND ↔ credits.
- Thông tin nạp tiền (tài khoản ngân hàng).
- Cấu hình giới hạn ảnh, timeout, v.v.

---

## 6) Thiết kế hệ thống (trung lập công nghệ)

### 6.1. Kiến trúc lớp
- **UI Layer**: trang server‑render + thành phần tương tác (reactive).
- **Application Layer**: controller/handler xử lý request, xác thực, phân quyền.
- **Domain/Service Layer**: dịch vụ tạo ảnh, ví, storage, quản trị model.
- **Job Queue**: xử lý tác vụ tạo ảnh nền (async).
- **Storage**: lưu ảnh lên kho lưu trữ đối tượng có hỗ trợ URL tạm thời (pre‑signed).
- **Database**: lưu user, style, ảnh, giao dịch, settings.

### 6.2. Luồng tạo ảnh (chi tiết)
1) UI gửi yêu cầu tạo ảnh.
2) Backend validate:
   - credits đủ.
   - style đang active.
   - input hợp lệ (ratio/size/advanced).
   - ảnh tham chiếu đủ slot + đúng giới hạn.
3) Trừ credits và ghi `generated_images` với `processing`.
4) Gửi job nền:
   - build prompt (base + options + input).
   - gọi API AI: create task → polling → nhận ảnh.
   - lưu ảnh vào storage, update `storage_path`.
5) UI polling trạng thái ảnh:
   - `completed` → trả URL.
   - `failed` → hiển thị lỗi + hoàn credits nếu cần.
6) Watchdog timeout: nếu quá lâu → mark failed + refund.

### 6.3. Prompt & tham số
- **Prompt cuối** = `base_prompt` + `prompt_fragment` từ options + mô tả user (nếu cho phép).
- Giới hạn độ dài prompt (tránh API reject).
- Tham số nâng cao chỉ hiển thị khi model hỗ trợ.
- Kích thước ảnh:
  - Ưu tiên **aspect ratio** nếu model hỗ trợ.
  - Nếu model chỉ hỗ trợ width/height: hệ thống map ratio → kích thước.
  - Kích thước tuân thủ giới hạn min/max/multiple theo model.

---

## 7) Tích hợp nhà cung cấp AI

### 7.1. Hợp đồng API (mô tả chức năng)
- **Tạo task**: gửi payload gồm prompt + tham số (ratio/size/seed/…).
- **Nhận task_id + polling_url**.
- **Polling kết quả**:
  - Trạng thái Ready → nhận ảnh (base64/URL).
  - Trạng thái Failed/Moderated/Not Found → trả lỗi.
- Có **backoff** khi gặp rate‑limit.

### 7.2. Ảnh tham chiếu
- Hỗ trợ nhiều input images nếu model cho phép.
- Có thể gửi ảnh dạng **base64** hoặc **URL**.
- Hạn chế tổng dung lượng payload và chặn URL nội bộ (SSRF).

---

## 8) Database (mô hình dữ liệu)

> Dưới đây là các bảng chính và cột quan trọng.

### 8.1. `users`
- thông tin đăng nhập cơ bản.
- `credits`, `is_admin`, `is_active`.

### 8.2. `styles`
- `name`, `slug`, `thumbnail_url`, `description`, `price`.
- `provider_model_id` (cột hiện tại: `bfl_model_id`), `legacy_provider_model_id` (cột hiện tại: `openrouter_model_id`).
- `base_prompt`, `config_payload` (JSON).
- `is_active`, `allow_user_custom_prompt`, `sort_order`.
- `image_slots` (JSON), `system_images` (JSON).
- `tag_id`.

### 8.3. `style_options`
- `style_id`, `label`, `group_name`, `prompt_fragment`.
- `icon`, `thumbnail` (nếu có).
- `sort_order`, `is_default`.

### 8.4. `tags`
- `name`, `color_from`, `color_to`, `icon`, `sort_order`, `is_active`.

### 8.5. `generated_images`
- `user_id`, `style_id`.
- `final_prompt`, `selected_options` (JSON), `user_custom_input`.
- `generation_params` (JSON).
- `storage_path`, `provider_task_id` (cột hiện tại: `bfl_task_id`), `legacy_provider_task_id` (cột hiện tại: `openrouter_id`).
- `status` (`pending|processing|completed|failed`), `error_message`.
- `credits_used`, timestamps, soft delete.

### 8.6. `wallet_transactions`
- `user_id`, `type` (credit/debit), `amount`.
- `balance_before`, `balance_after`.
- `reason`, `source`, `reference_id`.

### 8.7. `settings`
- `key`, `value`, `type`, `group`, `label`, `description`, `is_encrypted`.

### 8.8. Hạ tầng hệ thống
- `jobs`, `failed_jobs`, `personal_access_tokens`, `password_reset_tokens`…

---

## 9) Cấu hình & tham số hệ thống

Các cấu hình nên đặt trong **settings** hoặc môi trường vận hành:
- API key, base URL của AI provider.
- Timeout gọi API / polling.
- Bảng danh sách model + capabilities (supports ratio, size, seed…).
- Cấu hình tỉ lệ quy đổi tiền → credits.
- Giới hạn kích thước ảnh, dung lượng upload.
- Thông tin nạp tiền (ngân hàng, QR, nội dung chuyển khoản).
- Số ngày ảnh có thể hết hạn/hiển thị cảnh báo.

---

## 10) Bảo mật & an toàn

- Kiểm tra quyền sở hữu khi tải/xoá ảnh.
- Nội dung ảnh từ URL cần chống SSRF.
- API nội bộ yêu cầu **secret key** và có **rate‑limit**.
- Idempotency cho giao dịch nạp tiền và refund.

---

## 11) Vận hành & hiệu năng

- **Queue** dùng cho xử lý tạo ảnh nền (tránh block request).
- **Polling** trên UI để cập nhật trạng thái ảnh.
- **Cache**:
  - settings (giảm query).
  - danh sách model/capabilities.
  - pre‑signed URL ảnh (giảm request storage).
- Ảnh lưu trên storage riêng, truy cập bằng URL tạm thời.

---

## 12) Gợi ý tái xây dựng dự án tương đương

1) Chọn một **backend MVC + ORM**.
2) Chọn **UI server‑render + reactive components** hoặc SPA.
3) Chuẩn bị:
   - Database quan hệ (schema như phần 8).
   - Queue worker cho job tạo ảnh.
   - Storage đối tượng có hỗ trợ URL tạm thời (pre‑signed).
   - AI provider API (create task + polling).
4) Áp dụng các rule nghiệp vụ:
   - build prompt = base + option fragments + user input.
   - validate kích thước theo constraints.
   - trừ/hoàn credits có log và idempotency.
5) Xây admin để quản lý Style, Option, Tag, Settings, Users.

---

## 13) Checklist chức năng cốt lõi

- [ ] Duyệt Style: search/filter/sort/pagination.
- [ ] Studio: chọn options, upload ảnh tham chiếu, tạo ảnh.
- [ ] Async generation + polling + xử lý lỗi.
- [ ] Ví: số dư, nạp tiền, lịch sử giao dịch.
- [ ] Lịch sử ảnh: xem/tải/xoá.
- [ ] Admin: quản lý Style/Option/Tag/Users/Settings.
- [ ] API nội bộ: adjust wallet + payment callback.

---

Tài liệu này mô tả **logic và kiến trúc** thay vì phụ thuộc vào một stack cụ thể. Khi triển khai thực tế, bạn có thể chọn bất kỳ framework/libraries phù hợp nhưng vẫn đảm bảo các luồng, dữ liệu và hành vi giữ nguyên như mô tả trên.
