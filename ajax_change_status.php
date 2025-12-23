<?php
require_once 'functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện thao tác này.']);
    exit;
}

$userId = getCurrentUserId();

// Validate input
if (!isset($_POST['task_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cần thiết.']);
    exit;
}

$taskId = (int)$_POST['task_id'];
$status = trim($_POST['status']);

// Validate status
$validStatuses = ['Chưa làm', 'Đang thực hiện', 'Hoàn thành'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ.']);
    exit;
}

// Check if user can edit this task
if (!canEditTask($taskId, $userId)) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa nhiệm vụ này.']);
    exit;
}

try {
    global $pdo;

    // Update task status
    $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    $stmt->execute([$status, $taskId]);

    // Add history entry
    addTaskHistory($taskId, $userId, "Thay đổi trạng thái thành: $status");

    echo json_encode(['success' => true, 'message' => 'Trạng thái nhiệm vụ đã được cập nhật.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật trạng thái.']);
}
?>
