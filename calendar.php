<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Validate month and year
$month = max(1, min(12, (int)$month));
$year = max(1900, min(2100, (int)$year));

// Get tasks for the month
$tasks = getTasksForCalendar(getCurrentUserId(), $month, $year);

// Group tasks by date
$tasksByDate = [];
foreach ($tasks as $task) {
    $date = $task['deadline_date'];
    if (!isset($tasksByDate[$date])) {
        $tasksByDate[$date] = [];
    }
    $tasksByDate[$date][] = $task;
}

// Calendar generation
$firstDay = mktime(0, 0, 0, $month, 1, $year);
$lastDay = mktime(0, 0, 0, $month + 1, 0, $year);
$daysInMonth = date('t', $firstDay);
$startDayOfWeek = date('w', $firstDay);
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}
?>

<?php include 'header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Lịch nhiệm vụ - <?php echo date('F Y', $firstDay); ?></h3>
                <div>
                    <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="btn btn-outline-primary btn-sm">Trước</a>
                    <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="btn btn-outline-primary btn-sm">Sau</a>
                    <a href="?month=<?php echo date('m'); ?>&year=<?php echo date('Y'); ?>" class="btn btn-outline-secondary btn-sm">Hôm nay</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>CN</th>
                                <th>T2</th>
                                <th>T3</th>
                                <th>T4</th>
                                <th>T5</th>
                                <th>T6</th>
                                <th>T7</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $day = 1;
                            $currentDay = date('j');
                            $currentMonth = date('m');
                            $currentYear = date('Y');

                            for ($week = 0; $week < 6; $week++) {
                                echo '<tr>';
                                for ($weekday = 0; $weekday < 7; $weekday++) {
                                    if ($week == 0 && $weekday < $startDayOfWeek) {
                                        echo '<td class="text-muted"></td>';
                                    } elseif ($day > $daysInMonth) {
                                        echo '<td class="text-muted"></td>';
                                    } else {
                                        $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                        $isToday = ($day == $currentDay && $month == $currentMonth && $year == $currentYear);
                                        $cellClass = $isToday ? 'table-primary' : '';
                                        $tasksOnDay = $tasksByDate[$dateString] ?? [];

                                        echo '<td class="' . $cellClass . '">';
                                        echo '<div class="fw-bold">' . $day . '</div>';
                                        if (!empty($tasksOnDay)) {
                                            echo '<div class="small">';
                                            foreach ($tasksOnDay as $task) {
                                                $statusClass = '';
                                                if ($task['status'] === 'Done') {
                                                    $statusClass = 'text-success';
                                                } elseif ($task['status'] === 'In Progress') {
                                                    $statusClass = 'text-warning';
                                                } elseif (isTaskOverdue($task['deadline'], $task['status'])) {
                                                    $statusClass = 'text-danger';
                                                }
                                                echo '<div class="' . $statusClass . '">' . htmlspecialchars(substr($task['title'], 0, 20)) . (strlen($task['title']) > 20 ? '...' : '') . '</div>';
                                            }
                                            echo '</div>';
                                        }
                                        echo '</td>';
                                        $day++;
                                    }
                                }
                                echo '</tr>';
                                if ($day > $daysInMonth) break;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <h5>Chú thích:</h5>
                    <ul class="list-unstyled">
                        <li><span class="badge bg-primary">Hôm nay</span></li>
                        <li><span class="text-success">Hoàn thành</span></li>
                        <li><span class="text-warning">Đang thực hiện</span></li>
                        <li><span class="text-danger">Quá hạn</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
