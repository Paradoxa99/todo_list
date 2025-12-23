# Ứng dụng Quản lý Nhiệm vụ (Todo List Nâng cao)

Ứng dụng web quản lý nhiệm vụ cá nhân và chia sẻ được xây dựng bằng PHP thuần, không sử dụng framework.

## Tính năng chính

- **Đăng ký/Đăng nhập**: Tạo tài khoản với username, email, password, fullname. Sử dụng session để quản lý phiên đăng nhập.
- **Quản lý nhiệm vụ**: Tạo, chỉnh sửa, xóa nhiệm vụ với tiêu đề, mô tả, deadline, ưu tiên, tags.
- **Chia sẻ nhiệm vụ**: Giao nhiệm vụ cho người dùng khác bằng username hoặc email.
- **Theo dõi tiến độ**: 3 trạng thái nhiệm vụ (Todo, In Progress, Done).
- **Thông báo quá hạn**: Highlight nhiệm vụ có deadline đã qua mà chưa hoàn thành.
- **Lịch nhiệm vụ**: Xem nhiệm vụ theo ngày trên calendar đơn giản.
- **Tìm kiếm**: Tìm nhiệm vụ theo tiêu đề hoặc tag.
- **Lịch sử thay đổi**: Ghi lại các thay đổi trạng thái hoặc deadline.
- **AJAX**: Thay đổi trạng thái nhiệm vụ mà không reload trang.

## Yêu cầu hệ thống

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx web server
- XAMPP (khuyến nghị cho môi trường phát triển)

## Cài đặt

### Bước 1: Chuẩn bị môi trường

1. Cài đặt XAMPP từ https://www.apachefriends.org/
2. Khởi động Apache và MySQL trong XAMPP Control Panel

### Bước 2: Tạo database

1. Mở phpMyAdmin (http://localhost/phpmyadmin)
2. Tạo database mới có tên `nhiemvu` (UTF8_general_ci)
3. Import file `nhiemvu.sql` vào database vừa tạo

### Bước 3: Cấu hình ứng dụng

1. Sao chép toàn bộ thư mục dự án vào `C:\xampp\htdocs\nhiemvu`
2. Đảm bảo file `config.php` có cấu hình database đúng (mặc định đã đúng cho XAMPP)

### Bước 4: Truy cập ứng dụng

1. Mở trình duyệt và truy cập: http://localhost/nhiemvu/login.php
2. Đăng ký tài khoản mới hoặc sử dụng tài khoản mẫu:
   - Username: admin, Password: password
   - Username: user1, Password: password
   - Username: user2, Password: password
   - Username: user3, Password: password
   - Username: user4, Password: password
   - Username: user5, Password: password

## Cấu trúc thư mục

```
nhiemvu/
├── nhiemvu.sql          # Script tạo database và dữ liệu mẫu
├── config.php           # Cấu hình kết nối database
├── functions.php        # Các hàm tiện ích
├── header.php           # Header chung với Bootstrap
├── footer.php           # Footer chung với AJAX
├── login.php            # Trang đăng nhập
├── register.php         # Trang đăng ký
├── logout.php           # Đăng xuất
├── profile.php          # Chỉnh sửa thông tin cá nhân
├── dashboard.php        # Trang chủ hiển thị nhiệm vụ
├── add_task.php         # Thêm nhiệm vụ mới
├── edit_task.php        # Chỉnh sửa/xóa nhiệm vụ
├── task_detail.php      # Chi tiết nhiệm vụ và lịch sử
├── search.php           # Tìm kiếm nhiệm vụ
├── calendar.php         # Lịch xem nhiệm vụ
├── ajax_change_status.php # AJAX thay đổi trạng thái
└── README.md            # Tài liệu này
```

## Cấu trúc database

### Bảng users
- id (INT, PRIMARY KEY)
- username (VARCHAR(50), UNIQUE)
- email (VARCHAR(100), UNIQUE)
- password (VARCHAR(255), mã hóa)
- fullname (VARCHAR(100))
- created_at (DATETIME)

### Bảng tasks
- id (INT, PRIMARY KEY)
- title (VARCHAR(255))
- description (TEXT)
- priority (ENUM: Low, Medium, High)
- deadline (DATETIME)
- status (ENUM: Todo, In Progress, Done)
- creator_id (INT, FOREIGN KEY -> users.id)
- created_at (DATETIME)

### Bảng task_assignments
- id (INT, PRIMARY KEY)
- task_id (INT, FOREIGN KEY -> tasks.id)
- user_id (INT, FOREIGN KEY -> users.id)
- UNIQUE(task_id, user_id)

### Bảng task_tags
- id (INT, PRIMARY KEY)
- task_id (INT, FOREIGN KEY -> tasks.id)
- tag_name (VARCHAR(50))

### Bảng task_history
- id (INT, PRIMARY KEY)
- task_id (INT, FOREIGN KEY -> tasks.id)
- user_id (INT, FOREIGN KEY -> users.id)
- action (VARCHAR(255))
- timestamp (DATETIME)

## Bảo mật

- Mật khẩu được mã hóa bằng `password_hash()` và `password_verify()`
- Validate dữ liệu đầu vào nghiêm ngặt
- Sử dụng prepared statements để tránh SQL injection
- Session management cho authentication
- Kiểm tra quyền truy cập cho các thao tác chỉnh sửa nhiệm vụ

## Ghi chú phát triển

- Code được tổ chức rõ ràng với comment giải thích
- Sử dụng PDO cho database operations
- Xử lý múi giờ với DateTime trong PHP và DATETIME trong MySQL
- Giao diện responsive với Bootstrap 5
- AJAX cho trải nghiệm người dùng tốt hơn

## Tài khoản admin

Để có quyền xóa nhiệm vụ spam, bạn có thể thêm logic admin trong code hoặc sử dụng tài khoản 'admin' để quản lý.

## Hỗ trợ

Nếu gặp vấn đề trong quá trình cài đặt hoặc sử dụng, hãy kiểm tra:
1. Cấu hình PHP và MySQL trong XAMPP
2. Quyền truy cập file và thư mục
3. Cấu hình database trong config.php
4. Log lỗi của PHP và MySQL
