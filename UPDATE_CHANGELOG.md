# Cập nhật: Xem chi tiết nhiệm vụ, sửa lỗi deadline, và định dạng thời gian 24h

## Những thay đổi đã thực hiện

### 1. ✅ Thêm nút "Chi tiết" trên dashboard

- **File:** `dashboard.php`
- **Thay đổi:**
  - Thêm nút "Chi tiết" (button) trên mỗi thẻ nhiệm vụ
  - Người dùng có thể click nút để xem thông tin đầy đủ của nhiệm vụ
  - Trên trang chi tiết, có thể thấy ai được giao nhiệm vụ đó

### 2. ✅ Sửa lỗi validation deadline

- **File:** `functions.php`
- **Vấn đề:**
  - Deadline từ HTML input có format `Y-m-dTH:i` (ví dụ: 2023-12-25T14:30)
  - Nhưng validation function chỉ chấp nhận format `Y-m-d H:i` (với khoảng trắng)
  - Dẫn đến deadline hợp lệ (ở tương lai) vẫn bị reject
- **Giải pháp:**
  - Updated `validateDeadline()` để chấp nhận cả 2 format
  - Giờ có thể thêm nhiệm vụ với deadline ở tương lai bình thường

### 3. ✅ Đảm bảo hiển thị thời gian 24h

- **File:** `functions.php`
- **Format:** `d/m/Y H:i` (ví dụ: 25/12/2025 14:30)
- Đã thêm comment để rõ ràng đang dùng format 24h

### 4. ✅ Cải thiện hiển thị người tạo và người được giao

- **File:** `functions.php` - `getTaskById()`

  - Bây giờ truy vấn cũng lấy thông tin tên đầy đủ của người tạo
  - Tên creator được truyền qua `creator_username` và `creator_fullname`

- **File:** `task_detail.php`
  - Hiển thị tên đầy đủ của người tạo cùng username
  - Hiển thị danh sách người được giao theo dạng badge (badges)
  - Nếu chưa giao cho ai sẽ hiển thị "Chưa giao cho ai"

## Chi tiết các thay đổi code

### Validate Deadline (functions.php)

```php
function validateDeadline($deadline)
{
    // Handle both formats: Y-m-dTH:i (from input) and Y-m-d H:i (from database)
    $date = DateTime::createFromFormat('Y-m-d\TH:i', $deadline);
    if (!$date) {
        $date = DateTime::createFromFormat('Y-m-d H:i', $deadline);
    }
    if (!$date) {
        return false;
    }
    $now = new DateTime();
    return $date >= $now; // Allow current time or future
}
```

### Dashboard Card (dashboard.php)

```html
<div class="d-flex justify-content-between align-items-center gap-2">
  <a
    href="task_detail.php?id=<?php echo $task['id']; ?>"
    class="btn btn-sm btn-outline-primary"
    >Chi tiết</a
  >
  <div>
    <!-- Status dropdown here -->
  </div>
</div>
```

### Task Detail (task_detail.php)

```html
<div class="mb-3"><strong>Người tạo:</strong> Tên người tạo (username)</div>
<div class="mb-3">
  <strong>Được giao cho:</strong>
  <div class="mt-2">
    <span class="badge bg-primary">Người được giao 1 (username)</span>
    <span class="badge bg-primary">Người được giao 2 (username)</span>
  </div>
</div>
```

## Cách sử dụng

1. **Xem chi tiết nhiệm vụ:**

   - Trên dashboard, click nút "Chi tiết" ở bất kỳ thẻ nào
   - Trang chi tiết sẽ hiển thị:
     - Người tạo nhiệm vụ
     - Danh sách người được giao
     - Tất cả thông tin khác của nhiệm vụ

2. **Thêm nhiệm vụ với deadline:**

   - Chọn ngày và giờ ở tương lai
   - Deadline sẽ được validate chính xác
   - Format hiển thị: 25/12/2025 14:30 (24h)

3. **Xem ai làm nhiệm vụ:**
   - Trên dashboard: Click "Chi tiết"
   - Trên trang chi tiết: Thấy "Được giao cho: [tên người]"

## Kiểm tra

Hãy test các chức năng sau:

✅ Click nút "Chi tiết" trên dashboard → Xem được thông tin đầy đủ
✅ Thêm nhiệm vụ với deadline ở tương lai → Thành công (không bị reject)
✅ Xem deadline trong format 24h (ví dụ: 14:30 không phải 2:30 PM)
✅ Xem tên đầy đủ của người tạo
✅ Xem danh sách người được giao (nếu có)

## Lưu ý

- Thời gian hiển thị là 24h: từ 00:00 đến 23:59
- Deadline validation chấp nhận cả input từ HTML datetime-local và định dạng từ database
- Người được giao được hiển thị bằng badge (nhãn màu)
