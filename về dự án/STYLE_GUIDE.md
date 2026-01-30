# Hướng dẫn tạo Style đẹp & import nhanh (GenZ-friendly)

Tài liệu này giúp **ai cũng có thể tạo style đẹp** và **import hàng loạt** vào admin.
Mục tiêu: phong cách hiện đại, dễ viral, hợp thị hiếu GenZ, và dễ bảo trì về lâu dài.

---

## 1) Hiểu đúng về “Style”

Một **Style** gồm 4 phần chính:
1) **Thông tin hiển thị**: tên, mô tả, thumbnail, tag.
2) **Cấu hình tạo ảnh**: model, aspect ratio, tuỳ chọn nâng cao mặc định.
3) **Prompt cấu trúc**: base prompt + các slot (subject/action/style/...)
4) **Option cho người dùng**: list tuỳ chọn theo nhóm (group_name).

Nếu các phần này đồng bộ, người dùng chỉ cần chọn vài tuỳ chọn là ra ảnh đẹp.

---

## 2) Công thức tạo style “đẹp + dễ dùng”

### 2.1. Tư duy
- **GenZ thích nhanh – rõ – có vibe**: prompt ngắn, dễ chọn.
- **Một chủ đề chính**: tránh nhồi quá nhiều concept.
- **Thẩm mỹ mạng xã hội**: ánh sáng sạch, màu nổi, bố cục rõ.

### 2.2. Khung prompt chuẩn (gợi ý)
> **Subject + Action + Style + Context + Mood + Lighting + Color + Details + Technical**

Trong admin có các ô nhập sẵn cho từng slot:

| Slot | Ý nghĩa | Ví dụ nhanh |
|---|---|---|
| subject | Chủ thể | cô gái / chú mèo |
| action | Hành động | đang cười / đang chạy |
| style | Phong cách | cinematic / anime / editorial |
| context | Bối cảnh | phố đêm / studio |
| mood | Cảm xúc | ấm áp / bí ẩn |
| lighting | Ánh sáng | soft light / neon |
| color | Màu sắc | tông xanh tím |
| details | Chi tiết | tóc rõ nét / da mịn |
| technical | Kỹ thuật | 85mm, f/1.4, bokeh |
| custom | Tuỳ chọn | mô tả thêm |
| misc | Khác | tập trung vào mắt |

> **Lưu ý:** FLUX **không hỗ trợ negative prompt**. Hãy mô tả rõ điều bạn muốn xuất hiện trong prompt chính.

> Mẹo: chỉ cần 3–6 slot là đủ đẹp. Đừng điền tất cả nếu không cần.

---

## 3) Thiết kế Options thông minh

### 3.1. Nguyên tắc
- **Mỗi nhóm 1 lựa chọn** (single select) để tránh rối.
- **Mỗi nhóm chỉ 1 option mặc định**.
- Nhãn (label) phải **ngắn – dễ hiểu**.

### 3.2. Ví dụ option
```
label: "Áo hoodie"
group_name: "Trang phục"
prompt_fragment: "hoodie, casual streetwear"
```

---

## 4) Image Slots & System Images

### 4.1. Image Slots
- `key` **duy nhất**, không trùng nhau.
- `required: true` → bắt buộc upload đủ mới tạo ảnh.
- `label` hiển thị cho người dùng.
- Nếu dùng **inpaint/outpaint (Flux Fill)**:  
  - `image` = ảnh gốc  
  - `mask` = ảnh mask (đen = giữ, trắng = chỉnh)

### 4.2. System Images
- Dùng để “ép vibe” (tone, background, chất liệu...).
- Khuyến nghị dùng **URL công khai** (CDN/Storage public).
- Nếu file nội bộ, hãy upload lên storage và lấy URL public trước khi import.
- Có thể dùng **blob path** (BFL blob) nếu đã upload trước, ví dụ:  
  `blob_path: "blob:xxxxx/xxxxx"`

---

## 5) Đặt tên & “vibe” GenZ

**Format tên gợi ý:**
- “Neon City GenZ”
- “Mirror Selfie Aesthetic”
- “Soft Portrait 2K”

**Từ khoá vibe phổ biến:**
`aesthetic, dreamy, cinematic, soft light, neon, minimal, clean, magazine, editorial`

---

## 6) Cập nhật trend & style viral

- Quan sát TikTok/Instagram: filter hot, màu hot, bố cục hot.
- Lấy ý tưởng theo **mùa**: Noel, Tết, lễ hội, trend GenZ.
- Mỗi 2–4 tuần nên thêm style mới, bỏ style ít dùng.
- Cập nhật thumbnail đúng vibe (rõ, sáng, nhận diện nhanh).

---

## 7) Chuẩn JSON để import

### 7.1. Schema chuẩn
> JSON phải **hợp lệ** (không có comment, không thừa dấu phẩy).

```json
{
  "version": 1,
  "styles": [
    {
      "name": "Portrait Studio GenZ",
      "slug": "portrait-studio-genz",
      "thumbnail_url": "https://...",
      "description": "Phong cách chân dung trẻ trung",
      "price": 2,
      "sort_order": 0,
      "bfl_model_id": "flux-dev",
      "base_prompt": "Professional portrait, clean background",
      "tag": "HOT",
      "allow_user_custom_prompt": true,
      "is_active": true,
      "config_payload": {
        "aspect_ratio": "1:1",
        "prompt_strategy": "standard",
        "prompt_defaults": {
          "lighting": "soft studio light",
          "mood": "fresh, confident"
        }
      },
      "options": [
        {
          "label": "Áo hoodie",
          "group_name": "Trang phục",
          "prompt_fragment": "hoodie, casual streetwear",
          "is_default": true
        }
      ],
      "image_slots": [
        {
          "key": "image",
          "label": "Ảnh gốc",
          "description": "Ảnh cần chỉnh sửa",
          "required": true
        },
        {
          "key": "mask",
          "label": "Mask (tùy chọn)",
          "description": "Đen = giữ nguyên, trắng = chỉnh sửa",
          "required": false
        }
      ],
      "system_images": [
        {
          "label": "Mẫu nền",
          "description": "Giữ tone màu",
          "url": "https://..."
        },
        {
          "label": "System image (blob)",
          "description": "Ảnh tham chiếu lưu trên BFL blob",
          "blob_path": "blob:xxxxx/xxxxx"
        }
      ]
    }
  ]
}
```

### 7.2. Các trường quan trọng
- **name, base_prompt, bfl_model_id**: bắt buộc.
- **slug**: nên chuẩn URL và duy nhất.
- **options**: mỗi group chỉ 1 default.
- **image_slots.key**: duy nhất, không trùng.
- **system_images.url**: URL công khai (hoặc `blob_path`).
- **inpaint/outpaint**: dùng `bfl_model_id = flux-pro-1.0-fill` hoặc `flux-pro-1.0-fill-finetuned`.

---

## 8) Checklist trước khi import

- Thumbnail rõ vibe.
- Base prompt ngắn gọn.
- Options phân nhóm hợp lý.
- Prompt mặc định không quá dài.
- Đã test 1–2 ảnh xem ra ổn.

---

## 9) Ví dụ nhanh (GenZ style)

**Tên**: “Neon Night Portrait”
**Base prompt**: “Professional portrait, neon lighting, cinematic vibe”
**Defaults**:
- mood: “mysterious”
- lighting: “neon glow”
- color: “blue/purple palette”

---

Nếu bạn muốn, tôi có thể tạo sẵn file JSON 20–50 style mẫu để import ngay.
