<?php
// functions.php - Utility functions for the application

require_once 'config.php';

// Function to validate email
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate username (alphanumeric, underscore, 3-50 chars)
function validateUsername($username)
{
    return preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username);
}

// Function to validate password (at least 6 characters)
function validatePassword($password)
{
    return strlen($password) >= 6;
}

// Function to validate fullname (not empty, max 100 chars)
function validateFullname($fullname)
{
    return !empty(trim($fullname)) && strlen($fullname) <= 100;
}

// Function to validate deadline (must be valid datetime in future or present)
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

// Function to hash password
function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

// Function to verify password
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

// Function to check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Function to get current user ID
function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

// Function to get current user info
function getCurrentUser()
{
    if (!isLoggedIn()) return null;
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([getCurrentUserId()]);
    return $stmt->fetch();
}

// Function to check if current user is admin
function isAdmin($userId = null)
{
    if ($userId === null) {
        $userId = getCurrentUserId();
    }
    global $pdo;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    return $user && $user['role'] === 'admin';
}

// Function to get user role
function getUserRole($userId = null)
{
    if ($userId === null) {
        $userId = getCurrentUserId();
    }
    global $pdo;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    return $user ? $user['role'] : 'user';
}

// Function to check if task is overdue
function isTaskOverdue($deadline, $status)
{
    if ($status === 'Done') return false;
    $now = new DateTime();
    $deadlineDate = new DateTime($deadline);
    return $deadlineDate < $now;
}

// Function to format datetime for display (24h format)
function formatDatetime($datetime)
{
    $date = new DateTime($datetime);
    return $date->format('d/m/Y H:i'); // 24h format
}

// Function to get tasks for user (my tasks and assigned tasks)
// Admin can see all tasks, users can only see their own
function getUserTasks($userId, $type = 'all')
{
    global $pdo;
    $isAdmin = isAdmin($userId);

    if ($isAdmin) {
        // Admin sees all tasks regardless of type parameter
        $stmt = $pdo->prepare("SELECT * FROM tasks ORDER BY created_at DESC");
        $stmt->execute();
    } else {
        // Regular user sees only their own tasks
        if ($type === 'my') {
            // Tasks created by user
            $stmt = $pdo->prepare("SELECT * FROM tasks WHERE creator_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
        } elseif ($type === 'assigned') {
            // Tasks assigned to user
            $stmt = $pdo->prepare("
                SELECT t.* FROM tasks t
                INNER JOIN task_assignments ta ON t.id = ta.task_id
                WHERE ta.user_id = ? ORDER BY t.created_at DESC
            ");
            $stmt->execute([$userId]);
        } else {
            // All tasks related to user (created or assigned)
            $stmt = $pdo->prepare("
                SELECT DISTINCT t.* FROM tasks t
                LEFT JOIN task_assignments ta ON t.id = ta.task_id
                WHERE t.creator_id = ? OR ta.user_id = ? ORDER BY t.created_at DESC
            ");
            $stmt->execute([$userId, $userId]);
        }
    }

    $tasks = $stmt->fetchAll();

    // Add tags to each task
    foreach ($tasks as &$task) {
        $tagStmt = $pdo->prepare("SELECT tag_name FROM task_tags WHERE task_id = ?");
        $tagStmt->execute([$task['id']]);
        $task['tags'] = $tagStmt->fetchAll(PDO::FETCH_COLUMN);
    }

    return $tasks;
}

// Function to get task by ID with assignments and tags
function getTaskById($taskId)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT t.*, u.username as creator_username, u.fullname as creator_fullname
        FROM tasks t
        LEFT JOIN users u ON t.creator_id = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();
    if ($task) {
        // Get assignments
        $stmt = $pdo->prepare("
            SELECT u.username, u.fullname FROM users u
            INNER JOIN task_assignments ta ON u.id = ta.user_id
            WHERE ta.task_id = ?
        ");
        $stmt->execute([$taskId]);
        $task['assignments'] = $stmt->fetchAll();
        // Get tags
        $stmt = $pdo->prepare("SELECT tag_name FROM task_tags WHERE task_id = ?");
        $stmt->execute([$taskId]);
        $task['tags'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    return $task;
}

// Function to check if user can edit task
// Admin can edit any task, users can only edit their own (created or assigned)
function canEditTask($taskId, $userId)
{
    // Admin can edit any task
    if (isAdmin($userId)) {
        return true;
    }

    global $pdo;
    // User who created the task can edit it
    $stmt = $pdo->prepare("SELECT 1 FROM tasks WHERE id = ? AND creator_id = ?");
    $stmt->execute([$taskId, $userId]);
    if ($stmt->fetch()) return true;

    // User assigned to the task can edit it
    $stmt = $pdo->prepare("SELECT 1 FROM task_assignments WHERE task_id = ? AND user_id = ?");
    $stmt->execute([$taskId, $userId]);
    return $stmt->fetch() ? true : false;
}

// Function to add task history
function addTaskHistory($taskId, $userId, $action)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO task_history (task_id, user_id, action) VALUES (?, ?, ?)");
    $stmt->execute([$taskId, $userId, $action]);
}

// Function to get task history
function getTaskHistory($taskId)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT h.*, u.username, u.fullname FROM task_history h
        INNER JOIN users u ON h.user_id = u.id
        WHERE h.task_id = ? ORDER BY h.timestamp DESC
    ");
    $stmt->execute([$taskId]);
    return $stmt->fetchAll();
}

// Function to search tasks by title or tag
// Admin can search all tasks, users can only search their own
function searchTasks($userId, $query)
{
    global $pdo;
    $isAdmin = isAdmin($userId);

    if ($isAdmin) {
        // Admin searches all tasks
        $stmt = $pdo->prepare("
            SELECT DISTINCT t.* FROM tasks t
            LEFT JOIN task_tags tt ON t.id = tt.task_id
            WHERE (t.title LIKE ? OR tt.tag_name LIKE ?)
            ORDER BY t.created_at DESC
        ");
        $searchTerm = '%' . $query . '%';
        $stmt->execute([$searchTerm, $searchTerm]);
    } else {
        // User searches only their own tasks
        $stmt = $pdo->prepare("
            SELECT DISTINCT t.* FROM tasks t
            LEFT JOIN task_assignments ta ON t.id = ta.task_id
            LEFT JOIN task_tags tt ON t.id = tt.task_id
            WHERE (t.creator_id = ? OR ta.user_id = ?) AND (t.title LIKE ? OR tt.tag_name LIKE ?)
            ORDER BY t.created_at DESC
        ");
        $searchTerm = '%' . $query . '%';
        $stmt->execute([$userId, $userId, $searchTerm, $searchTerm]);
    }

    $tasks = $stmt->fetchAll();

    // Add tags to each task
    foreach ($tasks as &$task) {
        $tagStmt = $pdo->prepare("SELECT tag_name FROM task_tags WHERE task_id = ?");
        $tagStmt->execute([$task['id']]);
        $task['tags'] = $tagStmt->fetchAll(PDO::FETCH_COLUMN);
    }

    return $tasks;
}

// Function to get tasks for calendar (by date)
// Admin can see all tasks, users can only see their own
function getTasksForCalendar($userId, $month, $year)
{
    global $pdo;
    $isAdmin = isAdmin($userId);
    $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $month);
    $endDate = sprintf('%04d-%02d-31 23:59:59', $year, $month);

    if ($isAdmin) {
        // Admin sees all tasks
        $stmt = $pdo->prepare("
            SELECT t.*, DATE(t.deadline) as deadline_date FROM tasks t
            WHERE t.deadline BETWEEN ? AND ?
            ORDER BY t.deadline ASC
        ");
        $stmt->execute([$startDate, $endDate]);
    } else {
        // User sees only their own tasks
        $stmt = $pdo->prepare("
            SELECT t.*, DATE(t.deadline) as deadline_date FROM tasks t
            LEFT JOIN task_assignments ta ON t.id = ta.task_id
            WHERE (t.creator_id = ? OR ta.user_id = ?) AND t.deadline BETWEEN ? AND ?
            ORDER BY t.deadline ASC
        ");
        $stmt->execute([$userId, $userId, $startDate, $endDate]);
    }

    return $stmt->fetchAll();
}
