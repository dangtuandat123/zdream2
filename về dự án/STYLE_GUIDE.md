# Hướng dẫn tạo Style đẹp & import nhanh (GenZ-friendly)

Tài liệu này giúp **ai cũng có thể tạo style đẹp** và **import hàng loạt** vào admin.  
Mục tiêu: phong cách hiện đại, dễ viral, hợp thị hiếu GenZ.

---

## 1) Công thức tạo style “đẹp + dễ dùng”

### 1.1. Tư duy cơ bản
- **GenZ thích nhanh – rõ – có vibe**: chỉ cần mô tả ngắn là ra ảnh đẹp.
- **Tập trung vào 1 chủ đề chính** (tránh quá nhiều ý trong 1 style).
- **Ưu tiên thẩm mỹ mạng xã hội**: ánh sáng sạch, màu nổi bật, bố cục rõ.

### 1.2. Khung prompt tiêu chuẩn (gợi ý)
> **Subject + Action + Style + Context + Mood + Lighting + Color + Details + Technical**

Trong admin bạn có các ô nhập sẵn cho từng phần, chỉ cần điền từng ô:

| Slot | Ý nghĩa | Ví dụ nhanh |
|------|---------|------------|
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

> **Mẹo**: Chỉ cần 3–6 slot là đủ đẹp. Đừng điền tất cả nếu không cần.

---

## 2) Cách đặt tên & “vibe” GenZ

**Gợi ý format tên style:**
- “Neon City GenZ”
- “Mirror Selfie Aesthetic”
- “Soft Portrait 2K”

**Từ khoá vibe phổ biến:**
`aesthetic, dreamy, cinematic, soft light, neon, minimal, clean, magazine, editorial`

---

## 3) Thiết kế Options thông minh

### 3.1. Chia theo nhóm (group_name)
Nên dùng các tên nhóm **dễ hiểu** để hệ thống tự map đúng slot:
- `Chủ thể`, `Hành động`, `Phong cách`, `Bối cảnh`, `Cảm xúc`, `Ánh sáng`, `Màu sắc`, `Chi tiết`, `Kỹ thuật`

### 3.2. Ví dụ option:
```
label: "Áo hoodie"
group_name: "Trang phục"
prompt_fragment: "hoodie, casual streetwear"
```

---

## 4) Luôn cập nhật theo trend (gợi ý)

Để style luôn hợp xu hướng:
- Quan sát trend TikTok/Instagram (filter đang hot, màu hot, “vibe” hot).
- Cập nhật **thumbnail** theo đúng vibe.
- Mỗi 2–4 tuần nên thêm style mới, bỏ style cũ ít dùng.

---

## 5) Cấu trúc JSON để import

### 5.1. Schema chuẩn
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
          "key": "ref_1",
          "label": "Ảnh tham chiếu",
          "description": "Ảnh gương mặt rõ",
          "required": true
        }
      ],
      "system_images": [
        {
          "label": "Mẫu nền",
          "description": "Giữ tone màu",
          "url": "https://..."
        }
      ]
    }
  ]
}
```

### 5.2. Các trường quan trọng
- **name, base_prompt, bfl_model_id**: bắt buộc
- **config_payload.prompt_defaults**: nơi đặt từng slot prompt theo ô riêng
- **options**: giúp người dùng lựa chọn nhanh
- **system_images**: dùng URL, hệ thống sẽ tự tải ảnh làm tham chiếu

---

## 6) Checklist trước khi import

✅ Thumbnail rõ vibe  
✅ Base prompt đủ ngắn gọn  
✅ Options phân nhóm hợp lý  
✅ Prompt mặc định không quá dài  
✅ Đã test 1–2 ảnh xem ra ổn

---

## 7) Gợi ý mẫu style GenZ nhanh

**Tên**: “Neon Night Portrait”  
**Base prompt**: “Professional portrait, neon lighting, cinematic vibe”  
**Defaults**:  
- mood: “mysterious”  
- lighting: “neon glow”  
- color: “blue/purple palette”  

---

Nếu bạn muốn, tôi có thể tạo sẵn file JSON 20–50 style mẫu để import ngay.
