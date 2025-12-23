<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = getCurrentUserId();
$isAdmin = isAdmin($userId);
$taskId = $_GET['id'] ?? null;

if (!$taskId || !is_numeric($taskId)) {
    header('Location: dashboard.php');
    exit;
}

$task = getTaskById($taskId);

if (!$task || !canEditTask($taskId, $userId)) {
    header('Location: dashboard.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // Only creator (người giao) and admin can delete task
        // People assigned to task (người được giao) CANNOT delete
        if (!$isAdmin && $task['creator_id'] !== $userId) {
            $message = 'Chỉ người giao nhiệm vụ hoặc Admin mới có thể xóa!';
        } else {
            // Delete task
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("DELETE FROM task_history WHERE task_id = ?");
                $stmt->execute([$taskId]);
                $stmt = $pdo->prepare("DELETE FROM task_tags WHERE task_id = ?");
                $stmt->execute([$taskId]);
                $stmt = $pdo->prepare("DELETE FROM task_assignments WHERE task_id = ?");
                $stmt->execute([$taskId]);
                $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
                $stmt->execute([$taskId]);
                $pdo->commit();
                header('Location: dashboard.php?deleted=1');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = 'Có lỗi xảy ra khi xóa nhiệm vụ.';
            }
        }
    } else {
        // Update task
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $deadline = $_POST['deadline'] ?? '';
        $priority = $_POST['priority'] ?? 'Trung bình';
        $status = $_POST['status'] ?? 'Chưa làm';
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
        if (!in_array($priority, ['Thấp', 'Trung bình', 'Cao'])) {
            $errors[] = 'Ưu tiên không hợp lệ.';
        }
        if (!in_array($status, ['Chưa làm', 'Đang thực hiện', 'Hoàn thành'])) {
            $errors[] = 'Trạng thái không hợp lệ.';
        }

        // Validate assignments
        $validAssignments = [];
        if (!empty($assignments)) {
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

        if (empty($errors)) {
            $pdo->beginTransaction();
            try {
                // Update task
                $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, priority = ?, deadline = ?, status = ? WHERE id = ?");
                $stmt->execute([$title, $description, $priority, $deadline, $status, $taskId]);

                // Update tags
                $stmt = $pdo->prepare("DELETE FROM task_tags WHERE task_id = ?");
                $stmt->execute([$taskId]);
                foreach ($tags as $tag) {
                    if (!empty($tag)) {
                        $stmt = $pdo->prepare("INSERT INTO task_tags (task_id, tag_name) VALUES (?, ?)");
                        $stmt->execute([$taskId, $tag]);
                    }
                }

                // Update assignments
                $stmt = $pdo->prepare("DELETE FROM task_assignments WHERE task_id = ?");
                $stmt->execute([$taskId]);
                foreach ($validAssignments as $assignUserId) {
                    $stmt = $pdo->prepare("INSERT INTO task_assignments (task_id, user_id) VALUES (?, ?)");
                    $stmt->execute([$taskId, $assignUserId]);
                }

                // Add history
                $action = "Updated task";
                if ($status !== $task['status']) {
                    $action = "Changed status to $status";
                }
                addTaskHistory($taskId, $userId, $action);

                $pdo->commit();
                $message = 'Cập nhật nhiệm vụ thành công!';
                $task = getTaskById($taskId); // Refresh task data
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = 'Có lỗi xảy ra khi cập nhật nhiệm vụ.';
            }
        } else {
            $message = implode('<br>', $errors);
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Chỉnh sửa nhiệm vụ</h3>
                <?php if ($isAdmin || $task['creator_id'] === $userId): ?>
                    <form method="post" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa nhiệm vụ này?')">
                        <button type="submit" name="delete" class="btn btn-danger btn-sm">Xóa nhiệm vụ</button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>

                <?php
                // Show permission info for non-creators
                $isCreator = $task['creator_id'] === $userId;
                if (!$isCreator && !$isAdmin):
                ?>
                    <div class="alert alert-warning">
                        <strong>⚠️ Lưu ý:</strong> Bạn có thể chỉnh sửa nhiệm vụ này, nhưng chỉ người giao (<?php echo htmlspecialchars($task['creator_fullname']); ?>) mới có thể xóa.
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($task['description']); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="deadline" class="form-label">Deadline <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="deadline" name="deadline" value="<?php echo date('Y-m-d\TH:i', strtotime($task['deadline'])); ?>" required>
                            <div class="form-text">Phải là ngày giờ trong tương lai.</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="priority" class="form-label">Ưu tiên</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="Thấp" <?php echo $task['priority'] === 'Thấp' ? 'selected' : ''; ?>>Thấp</option>
                                <option value="Trung bình" <?php echo $task['priority'] === 'Trung bình' ? 'selected' : ''; ?>>Trung bình</option>
                                <option value="Cao" <?php echo $task['priority'] === 'Cao' ? 'selected' : ''; ?>>Cao</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Chưa làm" <?php echo $task['status'] === 'Chưa làm' ? 'selected' : ''; ?>>Chưa làm</option>
                                <option value="Đang thực hiện" <?php echo $task['status'] === 'Đang thực hiện' ? 'selected' : ''; ?>>Đang thực hiện</option>
                                <option value="Hoàn thành" <?php echo $task['status'] === 'Hoàn thành' ? 'selected' : ''; ?>>Hoàn thành</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags</label>
                        <input type="text" class="form-control" id="tags" name="tags" value="<?php echo htmlspecialchars(implode(', ', $task['tags'])); ?>" placeholder="tag1, tag2, tag3">
                        <div class="form-text">Ngăn cách bằng dấu phẩy.</div>
                    </div>
                    <div class="mb-3">
                        <label for="assignments" class="form-label">Giao cho (username hoặc email)</label>
                        <input type="text" class="form-control" id="assignments" name="assignments[]" value="<?php echo htmlspecialchars(implode(', ', array_column($task['assignments'], 'username'))); ?>" placeholder="user1@example.com, username2">
                        <div class="form-text">Ngăn cách bằng dấu phẩy. Để trống nếu không giao cho ai.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Cập nhật nhiệm vụ</button>
                    <a href="dashboard.php" class="btn btn-secondary">Hủy</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
