-- SQL script to create database 'nhiemvu', tables, and sample data

CREATE DATABASE IF NOT EXISTS nhiemvu;
USE nhiemvu;

-- Table: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table: tasks
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    priority ENUM('Thấp', 'Trung bình', 'Cao') DEFAULT 'Trung bình',
    deadline DATETIME,
    status ENUM('Chưa làm', 'Đang thực hiện', 'Hoàn thành') DEFAULT 'Chưa làm',
    creator_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: task_assignments
CREATE TABLE task_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(task_id, user_id)
);

-- Table: task_tags
CREATE TABLE task_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    tag_name VARCHAR(50) NOT NULL,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

-- Table: task_history
CREATE TABLE task_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


INSERT INTO users (username, email, password, fullname, role) VALUES
-- acc: admin pass: admin123  pass của user1-user5: pass123
('admin', 'admin@example.com', '$2y$10$BvtuyXRd.zD7nzF4t9nYCOHlLGQCXB8CNC077UKbtm1EQPaapj.3q', 'Administrator', 'admin'),
('user1', 'user1@example.com', '$2y$10$cnG1DW8Lb2Iu/IyPfoAfwO5JqDGK.bIzSz74GJe1jD/0Gdgcmy3pS', 'Nguyen Van A', 'user'),
('user2', 'user2@example.com', '$2y$10$cnG1DW8Lb2Iu/IyPfoAfwO5JqDGK.bIzSz74GJe1jD/0Gdgcmy3pS', 'Tran Thi B', 'user'),
('user3', 'user3@example.com', '$2y$10$cnG1DW8Lb2Iu/IyPfoAfwO5JqDGK.bIzSz74GJe1jD/0Gdgcmy3pS', 'Le Van C', 'user'),
('user4', 'user4@example.com', '$2y$10$cnG1DW8Lb2Iu/IyPfoAfwO5JqDGK.bIzSz74GJe1jD/0Gdgcmy3pS', 'Pham Thi D', 'user'),
('user5', 'user5@example.com', '$2y$10$cnG1DW8Lb2Iu/IyPfoAfwO5JqDGK.bIzSz74GJe1jD/0Gdgcmy3pS', 'Hoang Van E', 'user');


INSERT INTO tasks (title, description, priority, deadline, status, creator_id) VALUES
('Hoàn thành báo cáo tháng', 'Viết báo cáo tổng kết tháng 9', 'Cao', '2023-09-30 17:00:00', 'Hoàn thành', 1),
('Thiết kế logo mới', 'Thiết kế logo cho dự án mới', 'Thấp', '2023-10-15 18:00:00', 'Chưa làm', 4),
('Đọc sách kỹ năng', 'Đọc xong cuốn sách "Atomic Habits"', 'Thấp', '2023-10-20 20:00:00', 'Chưa làm', 5),
('Lập kế hoạch du lịch', 'Lên kế hoạch cho chuyến du lịch cuối năm', 'Trung bình', '2023-10-10 16:00:00', 'Đang thực hiện', 1),
('Cập nhật CV', 'Cập nhật CV với kinh nghiệm mới', 'Trung bình', '2023-10-08 14:00:00', 'Chưa làm', 2),
('Mua quà sinh nhật', 'Mua quà cho sinh nhật bạn bè', 'Thấp', '2023-10-03 10:00:00', 'Hoàn thành', 3),
('Học tiếng Anh', 'Ôn tập từ vựng tiếng Anh hàng ngày', 'Trung bình', '2023-10-12 19:00:00', 'Đang thực hiện', 4),
('Sửa chữa nhà cửa', 'Sửa chữa ống nước bị rò rỉ', 'Cao', '2023-09-28 11:00:00', 'Hoàn thành', 5),
('Viết bài blog', 'Viết bài về xu hướng công nghệ 2023', 'Trung bình', '2023-10-18 15:00:00', 'Chưa làm', 1),
('Tập thể dục', 'Đi bộ 30 phút mỗi ngày', 'Thấp', '2023-10-25 07:00:00', 'Đang thực hiện', 2),
('Gọi điện cho gia đình', 'Gọi điện hỏi thăm bố mẹ', 'Thấp', '2023-10-06 20:00:00', 'Hoàn thành', 3),
('Nghiên cứu thị trường', 'Nghiên cứu thị trường cho sản phẩm mới', 'Cao', '2023-10-14 13:00:00', 'Chưa làm', 4),
('Dọn dẹp phòng', 'Dọn dẹp và sắp xếp phòng làm việc', 'Thấp', '2023-10-09 12:00:00', 'Đang thực hiện', 5),
('Tham gia khóa học online', 'Hoàn thành khóa học về machine learning', 'Cao', '2023-10-22 17:00:00', 'Chưa làm', 1),
('Mua sắm hàng tuần', 'Mua thực phẩm và đồ dùng gia đình', 'Trung bình', '2023-10-04 16:00:00', 'Hoàn thành', 2),
('Viết kế poster', 'Thiết kế poster cho sự kiện công ty', 'Trung bình', '2023-10-11 11:00:00', 'Chưa làm', 3),
('Ôn tập toán', 'Ôn tập kiến thức toán cao cấp', 'Cao', '2023-10-16 14:00:00', 'Đang thực hiện', 4),
('Xem phim', 'Xem phim "Inception" và thảo luận', 'Thấp', '2023-10-07 21:00:00', 'Hoàn thành', 5),
('Chuẩn bị bài giảng', 'Chuẩn bị bài giảng cho lớp học', 'Cao', '2023-09-27 10:00:00', 'Hoàn thành', 1);

INSERT INTO task_assignments (task_id, user_id) VALUES
(1, 1), (1, 2),
(2, 2), (2, 3),
(3, 3), (3, 4),
(4, 4), (4, 5),
(5, 5), (5, 1),
(6, 1), (6, 2), (6, 3),
(7, 2), (7, 4),
(8, 3), (8, 5),
(9, 4), (9, 1),
(10, 5), (10, 2),
(11, 1), (11, 3),
(12, 2), (12, 4),
(13, 3), (13, 5),
(14, 4), (14, 1),
(15, 5), (15, 2),
(16, 1), (16, 3),
(17, 2), (17, 4),
(18, 3), (18, 5),
(19, 4), (19, 1);

INSERT INTO task_tags (task_id, tag_name) VALUES
(1, 'work'), (1, 'report'),
(2, 'design'), (2, 'logo'),
(3, 'personal'), (3, 'reading'),
(4, 'travel'), (4, 'planning'),
(5, 'career'), (5, 'cv'),
(6, 'personal'), (6, 'shopping'),
(7, 'learning'), (7, 'english'),
(8, 'home'), (8, 'repair'),
(9, 'writing'), (9, 'blog'),
(10, 'health'), (10, 'exercise'),
(11, 'personal'), (11, 'family'),
(12, 'work'), (12, 'research'),
(13, 'personal'), (13, 'cleaning'),
(14, 'learning'), (14, 'course'),
(15, 'shopping'), (15, 'weekly'),
(16, 'design'), (16, 'poster'),
(17, 'learning'), (17, 'math'),
(18, 'entertainment'), (18, 'movie'),
(19, 'work'), (19, 'teaching');

INSERT INTO task_history (task_id, user_id, action) VALUES
(1, 1, 'Tạo nhiệm vụ'),
(1, 1, 'Đã thay đổi trạng thái thành Hoàn thành'),
(2, 4, 'Tạo nhiệm vụ'),
(3, 5, 'Tạo nhiệm vụ'),
(4, 1, 'Tạo nhiệm vụ'),
(4, 1, 'Đã thay đổi trạng thái thành Đang thực hiện'),
(5, 2, 'Tạo nhiệm vụ'),
(6, 3, 'Tạo nhiệm vụ'),
(6, 3, 'Đã thay đổi trạng thái thành Hoàn thành'),
(7, 4, 'Tạo nhiệm vụ'),
(7, 4, 'Đã thay đổi trạng thái thành Đang thực hiện'),
(8, 5, 'Tạo nhiệm vụ'),
(8, 5, 'Đã thay đổi trạng thái thành Hoàn thành'),
(9, 1, 'Tạo nhiệm vụ'),
(10, 2, 'Tạo nhiệm vụ'),
(10, 2, 'Đã thay đổi trạng thái thành Đang thực hiện'),
(11, 3, 'Tạo nhiệm vụ'),
(11, 3, 'Đã thay đổi trạng thái thành Hoàn thành'),
(12, 4, 'Tạo nhiệm vụ'),
(13, 5, 'Tạo nhiệm vụ'),
(13, 5, 'Đã thay đổi trạng thái thành Đang thực hiện'),
(14, 1, 'Tạo nhiệm vụ'),
(15, 2, 'Tạo nhiệm vụ'),
(15, 2, 'Đã thay đổi trạng thái thành Hoàn thành'),
(16, 3, 'Tạo nhiệm vụ'),
(17, 4, 'Tạo nhiệm vụ'),
(17, 4, 'Đã thay đổi trạng thái thành Đang thực hiện'),
(18, 5, 'Tạo nhiệm vụ'),
(18, 5, 'Đã thay đổi trạng thái thành Hoàn thành'),
(19, 1, 'Tạo nhiệm vụ'),
(19, 1, 'Đã thay đổi trạng thái thành Hoàn thành');
