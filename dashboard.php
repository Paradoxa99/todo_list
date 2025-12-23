<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = getCurrentUserId();
$isAdmin = isAdmin($userId);
$tab = $_GET['tab'] ?? 'all'; // all, my, assigned

// Regular users can only see their own tasks (combined view)
if (!$isAdmin) {
    $tasks = getUserTasks($userId, 'all');
} else {
    // Admin can filter by type
    if ($tab === 'my') {
        $tasks = getUserTasks($userId, 'my');
    } elseif ($tab === 'assigned') {
        $tasks = getUserTasks($userId, 'assigned');
    } else {
        $tasks = getUserTasks($userId, 'all');
    }
}

// Group tasks by status
$tasksByStatus = [
    'Chưa làm' => [],
    'Đang thực hiện' => [],
    'Hoàn thành' => []
];

foreach ($tasks as $task) {
    $tasksByStatus[$task['status']][] = $task;
}
?>

<?php include 'header.php'; ?>

<?php if ($isAdmin): ?>
    <div class="row mb-3">
        <div class="col-12">
            <nav>
                <div class="nav nav-tabs" id="taskTabs" role="tablist">
                    <a class="nav-link <?php echo $tab === 'all' ? 'active' : ''; ?>" href="?tab=all">Tất cả nhiệm vụ</a>
                    <a class="nav-link <?php echo $tab === 'my' ? 'active' : ''; ?>" href="?tab=my">Nhiệm vụ của tôi</a>
                    <a class="nav-link <?php echo $tab === 'assigned' ? 'active' : ''; ?>" href="?tab=assigned">Nhiệm vụ được giao</a>
                </div>
            </nav>
        </div>
    </div>
<?php else: ?>
    <div class="row mb-3">
        <div class="col-12">
            <h5>Nhiệm vụ của bạn</h5>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <?php foreach ($tasksByStatus as $status => $statusTasks): ?>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?php echo $status; ?> (<?php echo count($statusTasks); ?>)</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($statusTasks)): ?>
                        <p class="text-muted">Không có nhiệm vụ nào.</p>
                    <?php else: ?>
                        <?php foreach ($statusTasks as $task): ?>
                            <?php
                            $isOverdue = isTaskOverdue($task['deadline'], $task['status']);
                            $cardClass = $isOverdue ? 'border-danger' : '';
                            $titleClass = $isOverdue ? 'text-danger' : '';
                            ?>
                            <div class="card mb-2 <?php echo $cardClass; ?>">
                                <div class="card-body p-2">
                                    <h6 class="card-title <?php echo $titleClass; ?>">
                                        <a href="task_detail.php?id=<?php echo $task['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($task['title']); ?>
                                        </a>
                                        <?php if ($isOverdue): ?>
                                            <span class="badge bg-danger">Quá hạn</span>
                                        <?php endif; ?>
                                    </h6>
                                    <p class="card-text small"><?php echo htmlspecialchars(substr($task['description'], 0, 50)); ?>...</p>
                                    <small class="text-muted d-block mb-2">Deadline: <?php echo formatDatetime($task['deadline']); ?></small>
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <a href="task_detail.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                                        <div>
                                            <?php if (canEditTask($task['id'], $userId)): ?>
                                                <select class="form-select form-select-sm" onchange="changeStatus(<?php echo $task['id']; ?>, this.value)">
                                                    <option value="Chưa làm" <?php echo $task['status'] === 'Chưa làm' ? 'selected' : ''; ?>>Chưa làm</option>
                                                    <option value="Đang thực hiện" <?php echo $task['status'] === 'Đang thực hiện' ? 'selected' : ''; ?>>Đang thực hiện</option>
                                                    <option value="Hoàn thành" <?php echo $task['status'] === 'Hoàn thành' ? 'selected' : ''; ?>>Hoàn thành</option>
                                                </select>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo $task['status']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($task['tags'])): ?>
                                        <div class="mt-2">
                                            <?php foreach ($task['tags'] as $tag): ?>
                                                <span class="badge bg-light text-dark"><?php echo htmlspecialchars($tag); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>
