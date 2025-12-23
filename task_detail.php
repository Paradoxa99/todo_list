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

if (!$task) {
    header('Location: dashboard.php');
    exit;
}

// Check if user has access to view this task
// Admin can view all tasks, users can only view their own
if (!$isAdmin && $task['creator_id'] !== $userId) {
    // Check if user is assigned to this task
    $stmt = $pdo->prepare("SELECT 1 FROM task_assignments WHERE task_id = ? AND user_id = ?");
    $stmt->execute([$taskId, $userId]);
    if (!$stmt->fetch()) {
        header('Location: dashboard.php');
        exit;
    }
}

$canEdit = canEditTask($taskId, $userId);
$history = getTaskHistory($taskId);
$isOverdue = isTaskOverdue($task['deadline'], $task['status']);
?>

<?php include 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                <?php if ($canEdit): ?>
                    <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-primary btn-sm">Chỉnh sửa</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($isOverdue): ?>
                    <div class="alert alert-danger">
                        <strong>Quá hạn!</strong> Deadline đã qua mà nhiệm vụ chưa hoàn thành.
                    </div>
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Trạng thái:</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars($task['status']); ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Ưu tiên:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($task['priority']); ?></span>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Deadline:</strong> <?php echo formatDatetime($task['deadline']); ?>
                </div>

                <div class="mb-3">
                    <strong>Mô tả:</strong>
                    <p><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                </div>

                <?php if (!empty($task['tags'])): ?>
                    <div class="mb-3">
                        <strong>Tags:</strong>
                        <?php foreach ($task['tags'] as $tag): ?>
                            <span class="badge bg-light text-dark"><?php echo htmlspecialchars($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <strong>Người tạo:</strong> <?php echo htmlspecialchars($task['creator_fullname'] ?? 'Unknown'); ?> (<?php echo htmlspecialchars($task['creator_username'] ?? ''); ?>)
                </div>

                <?php if (!empty($task['assignments'])): ?>
                    <div class="mb-3">
                        <strong>Được giao cho:</strong>
                        <div class="mt-2">
                            <?php foreach ($task['assignments'] as $assignment): ?>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($assignment['fullname']); ?> (<?php echo htmlspecialchars($assignment['username']); ?>)</span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="mb-3">
                        <strong>Được giao cho:</strong> <span class="text-muted">Chưa giao cho ai</span>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <strong>Ngày tạo:</strong> <?php echo formatDatetime($task['created_at']); ?>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">Lịch sử thay đổi</h5>
            </div>
            <div class="card-body">
                <?php if (empty($history)): ?>
                    <p class="text-muted">Chưa có lịch sử thay đổi.</p>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($history as $entry): ?>
                            <div class="timeline-item mb-3">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6><?php echo htmlspecialchars($entry['action']); ?></h6>
                                    <p class="mb-0 text-muted">
                                        Bởi <?php echo htmlspecialchars($entry['fullname']); ?> (<?php echo htmlspecialchars($entry['username']); ?>)
                                        lúc <?php echo formatDatetime($entry['timestamp']); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -22px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #007bff;
        border: 2px solid #fff;
    }

    .timeline-content h6 {
        margin-bottom: 5px;
        font-size: 14px;
    }
</style>

<?php include 'footer.php'; ?>
