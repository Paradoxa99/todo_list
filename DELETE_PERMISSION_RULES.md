# Quy định Xoá Nhiệm Vụ

## 📋 Quy luật xoá nhiệm vụ

Chỉ có **2 loại người** có thể xoá một nhiệm vụ:

### 1️⃣ **Người giao nhiệm vụ (Creator)**

- Là người tạo/giao nhiệm vụ
- Có quyền xoá nhiệm vụ của họ
- Nút "Xóa nhiệm vụ" sẽ hiển thị

### 2️⃣ **Admin**

- Admin có quyền xoá BẤT KỲ nhiệm vụ nào
- Nút "Xóa nhiệm vụ" sẽ luôn hiển thị
- Có toàn quyền quản lý hệ thống

### ❌ **Người được giao (NOT allowed)**

- Người được giao nhiệm vụ KHÔNG có quyền xoá
- Họ có thể chỉnh sửa (edit) nhiệm vụ
- Nhưng KHÔNG thể xoá
- Nút "Xóa nhiệm vụ" sẽ ẩn đi
- Sẽ hiển thị thông báo cảnh báo

---

## 📝 Ví dụ thực tế

### Tình huống 1: Admin xem nhiệm vụ của người khác

```
Admin (Quản trị viên)
├─ Thấy nút "Xóa nhiệm vụ" → CÓ thể xoá
├─ Lý do: Admin có quyền cao nhất
```

### Tình huống 2: Người giao xem nhiệm vụ của họ

```
User1 (Người giao)
├─ Task do User1 tạo
├─ Thấy nút "Xóa nhiệm vụ" → CÓ thể xoá
├─ Lý do: User1 là người tạo
```

### Tình huống 3: Người được giao xem nhiệm vụ

```
User2 (Người được giao)
├─ Task do User1 tạo, giao cho User2
├─ KHÔNG thấy nút "Xóa nhiệm vụ" → KHÔNG thể xoá
├─ Nhưng có thể chỉnh sửa (edit)
├─ Thông báo: "⚠️ Chỉ người giao mới có thể xóa"
```

---

## 🔐 Bảo mật

### Kiểm tra Server-side

```
Nếu người dùng cố xoá nhiệm vụ:
1. Check: Là admin? → CÓ → Cho phép xoá
2. Check: Là creator (người giao)? → CÓ → Cho phép xoá
3. Check: Không phải admin hoặc creator? → KHÔNG → Từ chối với lỗi
   "Chỉ người giao nhiệm vụ hoặc Admin mới có thể xóa!"
```

---

## 🎯 Quyền hạn chi tiết

| Hành động     | Admin     | Creator   | Người được giao |
| ------------- | --------- | --------- | --------------- |
| Xem           | ✅ Tất cả | ✅ Của họ | ✅ Của họ       |
| Chỉnh sửa     | ✅ Tất cả | ✅ Của họ | ✅ Của họ       |
| Xoá           | ✅ Tất cả | ✅ Của họ | ❌ KHÔNG        |
| Giao cho khác | ✅ Tất cả | ❌ KHÔNG  | ❌ KHÔNG        |

---

## 💬 Thông báo cho người dùng

### Khi người được giao cố xem trang edit:

```
⚠️ Lưu ý: Bạn có thể chỉnh sửa nhiệm vụ này,
nhưng chỉ người giao [Tên Người] mới có thể xóa.
```

### Khi cố gắng xoá nhưng không được phép:

```
❌ Chỉ người giao nhiệm vụ hoặc Admin mới có thể xóa!
```

---

## 📂 Code Implementation

### File: edit_task.php

**Hiển thị delete button:**

```php
<?php if ($isAdmin || $task['creator_id'] === $userId): ?>
    <button type="submit" name="delete" class="btn btn-danger btn-sm">
        Xóa nhiệm vụ
    </button>
<?php endif; ?>
```

**Kiểm tra quyền khi xoá:**

```php
if (isset($_POST['delete'])) {
    // Only creator (người giao) and admin can delete task
    if (!$isAdmin && $task['creator_id'] !== $userId) {
        $message = 'Chỉ người giao nhiệm vụ hoặc Admin mới có thể xóa!';
    } else {
        // Tiến hành xoá...
    }
}
```

**Cảnh báo cho người được giao:**

```php
<?php
    $isCreator = $task['creator_id'] === $userId;
    if (!$isCreator && !$isAdmin):
?>
    <div class="alert alert-warning">
        ⚠️ Lưu ý: Bạn có thể chỉnh sửa nhiệm vụ này,
        nhưng chỉ người giao mới có thể xóa.
    </div>
<?php endif; ?>
```

---

## ✅ Kiểm tra

Hãy test các kịch bản:

### Test 1: Xoá với tư cách creator

```
1. Login thành User1 (người giao)
2. Vào edit task của User1
3. ✓ Nút "Xóa nhiệm vụ" hiển thị
4. Click xoá → Thành công
```

### Test 2: Xoá với tư cách người được giao

```
1. Login thành User2 (người được giao)
2. Vào edit task do User1 tạo, giao cho User2
3. ✗ Nút "Xóa nhiệm vụ" KHÔNG hiển thị
4. ✓ Thông báo cảnh báo: "Chỉ người giao mới có thể xóa"
```

### Test 3: Xoá với tư cách admin

```
1. Login thành admin
2. Vào edit bất kỳ task nào
3. ✓ Nút "Xóa nhiệm vụ" luôn hiển thị
4. Click xoá → Thành công
```

---

## 📌 Lợi ích

✅ **An toàn:** Người được giao không vô tình xoá task
✅ **Rõ ràng:** Ai có quyền xoá được hiển thị rõ
✅ **Công bằng:** Chỉ người giao có thể xoá, không phải người khác
✅ **Chuyên nghiệp:** Quản lý quyền hạn hợp lý
