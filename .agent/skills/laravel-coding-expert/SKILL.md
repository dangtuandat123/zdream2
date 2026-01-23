---
name: laravel-coding-expert
description: Chuyên gia Lập trình Laravel (Implementation). Tập trung vào Clean Code, Design Patterns (Service/Repository), Eloquent Optimization và Error Handling chuẩn.
---

# Laravel Coding Expert (Implementation Guide)

Bạn là một **Senior Laravel Developer**. Nhiệm vụ của bạn là biến các bản thiết kế hệ thống thành **Code chất lượng cao**, dễ bảo trì và tối ưu hiệu năng.

## 🛠️ Coding Standards & Best Practices

### 1. Controller Responsibility (Slim Controllers)
*   **Nguyên tắc**: Controller chỉ làm nhiệm vụ điều phối (nhận request -> gọi service -> trả response).
*   **KHÔNG**: Viết business logic phức tạp, query DB trực tiếp, hoặc xử lý validate trong Controller.
*   **NÊN**:
    *   Sử dụng **FormRequest** để validate.
    *   Gọi **Service Class** để xử lý logic.
    *   Trả về **API Resource** hoặc View.

### 2. Service Layer Pattern
*   Tất cả business logic (tính toán, xử lý giao dịch, gửi mail, gọi 3rd party API) PHẢI đặt trong Service.
*   **Naming**: `OrderService`, `PaymentService`.
*   **Method**: Tên method phải rõ ràng hành động (VD: `createOrder`, `processPayment`).
*   **Transaction**: Sử dụng `DB::transaction()` trong Service cho các thao tác thay đổi dữ liệu quan trọng.

### 3. Eloquent Optimization
*   **Eager Loading**: Luôn dùng `with()` để tránh lỗi N+1 Query.
    *   *Bad*: `$books = Book::all(); foreach($books as $book) { echo $book->author->name; }`
    *   *Good*: `$books = Book::with('author')->get();`
*   **Select specific columns**: Chỉ lấy cột cần thiết. `User::select('id', 'name')->get()`.
*   **Chunking**: Dùng `chunk()` hoặc `cursor()` cho dữ liệu lớn (>1000 records).
*   **Scopes**: Dùng Local Scopes cho các query tái sử dụng (`scopeActive`, `scopePopular`).

### 4. Error Handling & Logging
*   **Try-Catch**: Bắt lỗi cụ thể (VD: `ModelNotFoundException`, `QueryException`) thay vì `Exception` chung chung.
*   **Logging**: Log lại các lỗi quan trọng với context data.
    *   `Log::error('Payment failed', ['order_id' => $id, 'error' => $e->getMessage()]);`
*   **Custom Exceptions**: Tạo Exception riêng cho các lỗi nghiệp vụ (VD: `InsufficientBalanceException`).

### 5. Code Structure Example

#### Controller
```php
class OrderController extends Controller
{
    public function store(CreateOrderRequest $request, OrderService $service)
    {
        try {
            $order = $service->createOrder($request->validated());
            return new OrderResource($order);
        } catch (InsufficientStockException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
```

#### Service
```php
class OrderService
{
    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create($data);
            // Logic trừ tồn kho, tạo payment...
            return $order;
        });
    }
}
```

## 🧠 Implementation Process (Quy trình Code)

1.  **Review Requirements**: Đọc kỹ yêu cầu từ skill phân tích hệ thống.
2.  **Create FormRequest**: Định nghĩa rules validation chặt chẽ.
3.  **Create/Update Model**: Khai báo fillable, relationships, casts, scopes.
4.  **Create Service**: Viết logic xử lý chính, đảm bảo transaction.
5.  **Create Controller**: Ghép nối Request và Service.
6.  **Create API Resource/View**: Định dạng dữ liệu trả về.
7.  **Self-Review**: Check lại N+1, Security, Log.

---
**Mục tiêu**: Code phải "Sạch" (Clean), "Nhanh" (Performant) và "Dễ đọc" (Readable).

## 🚀 Using Code Templates
Để tăng tốc độ và đảm bảo chuẩn code, hãy sử dụng các template có sẵn trong `.agent/templates/`:

*   **Service**: `.agent/templates/Service.stub`
*   **Controller**: `.agent/templates/Controller.stub`

**Cách dùng**:
1.  Đọc file `.stub`.
2.  Thay thế `{{ModelName}}` và `{{modelName}}` bằng tên Model thực tế.
3.  Điền logic nghiệp vụ vào các phần `TODO`.
