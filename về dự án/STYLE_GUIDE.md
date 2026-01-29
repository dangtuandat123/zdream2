# Hướng dẫn tạo Style đẹp & import nhanh (GenZ‑friendly, chuẩn A‑Z)

Tài liệu này giúp **bất kỳ ai** cũng có thể tạo style chất lượng cao, dễ viral và dễ import vào admin.  
Mục tiêu: **dễ hiểu – dễ dùng – ra ảnh đẹp ngay** với phong cách hợp thị hiếu GenZ.

---

## 0) Checklist nhanh trước khi làm
- ✅ Biết rõ style muốn phục vụ **1 chủ đề chính** (tránh lan man).
- ✅ Có 3–5 ví dụ ảnh tham khảo (Pinterest/Instagram/TikTok).
- ✅ Xác định **vibe** + **màu chủ đạo** + **bố cục**.
- ✅ Chọn model phù hợp (chất lượng hoặc tốc độ).
- ✅ Chuẩn bị thumbnail rõ vibe + đẹp ngay từ card.

---

## 1) Chọn model phù hợp (cực quan trọng)

> Mục tiêu: **đúng model → đúng chất lượng → đúng chi phí**.

Gợi ý lựa chọn (tùy nhu cầu thực tế):
- **Model thiên về chất lượng cao**: dùng cho ảnh chân dung, editorial, quảng cáo, ảnh sản phẩm.
- **Model thiên về tốc độ**: dùng cho trải nghiệm nhanh, thử nghiệm, ảnh casual/social.
- **Model hỗ trợ ảnh tham chiếu**: dùng cho style cần giữ gương mặt/pose/texture.
- **Model hỗ trợ tuỳ chỉnh nâng cao** (width/height/steps/guidance…): dùng cho style cần kiểm soát kỹ.

> **Nguyên tắc:**  
> - Style “premium/viral” → ưu tiên chất lượng.  
> - Style “nhanh/giải trí” → ưu tiên tốc độ.  
> - Style “giữ mặt/ảnh tham chiếu” → model có input image.

---

## 2) Kiến trúc Prompt: chuẩn hoá để AI hiểu nhanh

### 2.1. Prompt template (khung ghép)
Hệ thống hỗ trợ placeholder để ghép prompt, phần nào trống sẽ tự bỏ qua:

```
{{base}}, {{subject}}, {{action}}, {{style}}, {{context}}, {{mood}}, {{lighting}}, {{color}}, {{details}}, {{technical}}, {{custom}}, {{misc}}
```

### 2.2. Ý nghĩa từng phần (dễ hiểu – dễ nhập)
| Slot | Ý nghĩa | Ví dụ gợi ý |
|------|--------|-------------|
| **base** | Prompt nền chung (xương sống) | “highly detailed, clean composition” |
| **subject** | Chủ thể chính | “cô gái”, “mèo”, “nhân vật chibi” |
| **action** | Hành động | “đang cười”, “đang chạy” |
| **style** | Phong cách hình ảnh | “cinematic”, “anime”, “editorial” |
| **context** | Bối cảnh | “phố đêm”, “studio trắng” |
| **mood** | Cảm xúc | “ấm áp”, “bí ẩn” |
| **lighting** | Ánh sáng | “soft light”, “neon glow” |
| **color** | Màu chủ đạo | “tông tím hồng”, “pastel” |
| **details** | Chi tiết nhấn mạnh | “da mịn”, “bokeh đẹp” |
| **technical** | Góc máy/kỹ thuật | “85mm, f/1.4, shallow DOF” |
| **custom** | Tuỳ chỉnh riêng (admin) | “outfit Y2K” |
| **misc** | Phụ trợ khác | “focus on eyes” |

> **Mẹo quan trọng:**  
> - Không cần điền hết. 4–6 slot tốt nhất.  
> - Base + Subject + Style + Lighting thường đủ đẹp.  
> - Đừng nhồi quá nhiều mô tả → ảnh bị rối.

---

## 3) Thiết kế Options (cực quan trọng với UX)

### 3.1. Nhóm option hợp lý
Nên đặt tên nhóm **dễ hiểu, phổ thông**:
- Chủ thể
- Hành động
- Phong cách
- Bối cảnh
- Cảm xúc
- Ánh sáng
- Màu sắc
- Chi tiết
- Kỹ thuật

### 3.2. Quy tắc tạo option
- **Label ngắn** (1–3 từ).
- **Prompt fragment rõ nghĩa** (đừng viết quá dài).
- **1 nhóm chỉ 4–8 option** để dễ chọn.
- **Option mặc định** nên an toàn và đẹp.

Ví dụ option chuẩn:
```
label: "Hoodie"
group_name: "Trang phục"
prompt_fragment: "hoodie, casual streetwear"
```

---

## 4) Ảnh tham chiếu & System Image

### 4.1. Khi nào cần ảnh tham chiếu
- Style yêu cầu giữ gương mặt người dùng.
- Style cần texture hoặc pose cố định.

### 4.2. System Image (ảnh nền chuẩn)
System image nên dùng khi bạn muốn **đồng bộ tone & vibe** cho style.

Ví dụ:
- Style “neo‑city” → system image là background thành phố neon.
- Style “studio portrait” → system image là nền studio trắng.

---

## 5) Cách xác định giá Style (Xu)

Gợi ý:
- Style “nhanh/đơn giản” → giá thấp.
- Style “premium/chất lượng cao” → giá cao hơn.
- Style có nhiều option nâng cao → tăng giá nhẹ.

> **Nguyên tắc:** Người dùng luôn sẵn sàng trả nhiều hơn nếu ảnh “ra đẹp ngay lần đầu”.

---

## 6) Tìm style viral theo mùa (có hệ thống)

### 6.1. Mùa & dịp hot
- Tết, Valentine, 8/3, 20/10
- Noel, Halloween, Back‑to‑school
- Summer travel, beach, festival

### 6.2. Nguồn trend
- TikTok: filter, effect, color trend
- Instagram: aesthetic theme, creator style
- Pinterest: moodboard, color palette

### 6.3. Công thức “trend score”
Chọn style đạt **≥ 7/10**:
- 3 điểm: dễ viral (màu nổi, vibe lạ)
- 2 điểm: hợp mùa/dịp
- 2 điểm: dễ dùng (prompt ngắn)
- 1 điểm: dễ chia sẻ (mạng xã hội đẹp)

---

## 7) Quy trình test trước khi đưa lên

1. Chọn 3 ảnh test khác nhau
2. Test với 2–3 option
3. So sánh ảnh → chỉnh base prompt hoặc defaults
4. Đảm bảo **đẹp ≥ 80%** trước khi public

---

## 8) JSON Import chuẩn (để nhập nhanh)

```json
{
  "version": 1,
  "styles": [
    {
      "name": "Neon Night Portrait",
      "slug": "neon-night-portrait",
      "thumbnail_url": "https://...",
      "description": "Chân dung neon vibe thành phố đêm",
      "price": 2,
      "sort_order": 0,
      "bfl_model_id": "flux-2-pro",
      "base_prompt": "portrait, cinematic lighting, clean composition",
      "tag": "HOT",
      "allow_user_custom_prompt": true,
      "is_active": true,
      "config_payload": {
        "aspect_ratio": "1:1",
        "prompt_strategy": "standard",
        "prompt_defaults": {
          "lighting": "neon glow",
          "mood": "mysterious"
        }
      },
      "options": [
        {
          "label": "Hoodie",
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
          "label": "Nền neon",
          "description": "Giữ tone neon",
          "url": "https://..."
        }
      ]
    }
  ]
}
```

---

## 9) Kết luận
Style đẹp nhất không phải style dài nhất, mà là style **đúng vibe, đúng model, đúng prompt**.

Nếu cần mình có thể tạo sẵn 20–50 style mẫu (JSON import) để bạn nhập ngay.
