<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = getCurrentUserId();
$query = trim($_GET['q'] ?? '');
$results = [];

if (!empty($query)) {
    $results = searchTasks($userId, $query);
}
?>

<?php include 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tìm kiếm nhiệm vụ</h3>
            </div>
            <div class="card-body">
                <form method="get" class="mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" placeholder="Tìm theo tiêu đề hoặc tag..." value="<?php echo htmlspecialchars($query); ?>" required>
                        <button class="btn btn-primary" type="submit">Tìm kiếm</button>
                    </div>
                </form>

                <?php if (!empty($query)): ?>
                    <h5>Kết quả tìm kiếm cho "<?php echo htmlspecialchars($query); ?>"</h5>
                    <?php if (empty($results)): ?>
                        <p class="text-muted">Không tìm thấy nhiệm vụ nào.</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($results as $task): ?>
                                <?php
                                $isOverdue = isTaskOverdue($task['deadline'], $task['status']);
                                $cardClass = $isOverdue ? 'border-danger' : '';
                                $titleClass = $isOverdue ? 'text-danger' : '';
                                ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card <?php echo $cardClass; ?>">
                                        <div class="card-body">
                                            <h6 class="card-title <?php echo $titleClass; ?>">
                                                <a href="task_detail.php?id=<?php echo $task['id']; ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($task['title']); ?>
                                                </a>
                                                <?php if ($isOverdue): ?>
                                                    <span class="badge bg-danger">Quá hạn</span>
                                                <?php endif; ?>
                                            </h6>
                                            <p class="card-text small"><?php echo htmlspecialchars(substr($task['description'], 0, 100)); ?>...</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Deadline: <?php echo formatDatetime($task['deadline']); ?></small>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($task['status']); ?></span>
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
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
