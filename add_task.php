<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = getCurrentUserId();
$isAdmin = isAdmin($userId);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $deadline = $_POST['deadline'] ?? '';
    $priority = $_POST['priority'] ?? 'Medium';
    $tags = array_map('trim', explode(',', $_POST['tags'] ?? ''));
    $assignments = $_POST['assignments'] ?? [];

    // Validation
    $errors = [];
    if (empty($title)) {
        $errors[] = 'Tiêu đề không được để trống.';
    }
    if (!validateDeadline($deadline)) {
        $errors[] = 'Deadline phải là ngày giờ hợp lệ và không được trong quá khứ.';
    }
    if (!in_array($priority, ['Low', 'Medium', 'High'])) {
        $errors[] = 'Ưu tiên không hợp lệ.';
    }

    // Validate assignments (check if users exist)
    // Only admin can assign tasks to others, regular users can't assign to anyone
    $validAssignments = [];
    if (!empty($assignments) && implode(',', $assignments) !== '') {
        if (!$isAdmin) {
            $errors[] = 'Bạn không có quyền giao nhiệm vụ cho người khác. Chỉ admin mới có quyền này.';
        } else {
            foreach ($assignments as $assignment) {
                $assignment = trim($assignment);
                if (!empty($assignment)) {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                    $stmt->execute([$assignment, $assignment]);
                    $user = $stmt->fetch();
                    if ($user) {
                        $validAssignments[] = $user['id'];
                    } else {
                        $errors[] = "Người dùng '$assignment' không tồn tại.";
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        // Insert task
        $stmt = $pdo->prepare("INSERT INTO tasks (title, description, priority, deadline, creator_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $priority, $deadline, $userId]);
        $taskId = $pdo->lastInsertId();

        // Insert tags
        foreach ($tags as $tag) {
            if (!empty($tag)) {
                $stmt = $pdo->prepare("INSERT INTO task_tags (task_id, tag_name) VALUES (?, ?)");
                $stmt->execute([$taskId, $tag]);
            }
        }

        // Insert assignments
        foreach ($validAssignments as $assignUserId) {
            $stmt = $pdo->prepare("INSERT INTO task_assignments (task_id, user_id) VALUES (?, ?)");
            $stmt->execute([$taskId, $assignUserId]);
        }

        // Add history
        addTaskHistory($taskId, $userId, 'Created task');

        $message = 'Thêm nhiệm vụ thành công! <a href="dashboard.php">Quay lại dashboard</a>';
    } else {
        $message = implode('<br>', $errors);
    }
}
?>

<?php include 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Thêm nhiệm vụ mới</h3>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="deadline" class="form-label">Deadline <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="deadline" name="deadline" required>
                            <div class="form-text">Phải là ngày giờ trong tương lai.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Ưu tiên</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" class="form-control" id="tags" name="tags" placeholder="tag1, tag2, tag3">
                        <div class="form-text">Ngăn cách bằng dấu phẩy.</div>
                    </div>
                    <?php if ($isAdmin): ?>
                        <div class="mb-3">
                            <label for="assignments" class="form-label">Giao cho (username hoặc email)</label>
                            <input type="text" class="form-control" id="assignments" name="assignments[]" placeholder="user1@example.com, username2">
                            <div class="form-text">Ngăn cách bằng dấu phẩy. Để trống nếu không giao cho ai.</div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Chỉ Admin có thể giao nhiệm vụ cho người khác. Bạn đang tạo nhiệm vụ riêng cho mình.
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">Thêm nhiệm vụ</button>
                    <a href="dashboard.php" class="btn btn-secondary">Hủy</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
