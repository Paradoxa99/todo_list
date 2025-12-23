<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$message = '';
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $errors = [];
    if (!validateFullname($fullname)) {
        $errors[] = 'Họ tên không được để trống và tối đa 100 ký tự.';
    }

    // If changing password
    if (!empty($newPassword)) {
        if (!verifyPassword($currentPassword, $user['password'])) {
            $errors[] = 'Mật khẩu hiện tại không đúng.';
        }
        if (!validatePassword($newPassword)) {
            $errors[] = 'Mật khẩu mới phải ít nhất 6 ký tự.';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Mật khẩu xác nhận không khớp.';
        }
    }

    if (empty($errors)) {
        // Update fullname
        $stmt = $pdo->prepare("UPDATE users SET fullname = ? WHERE id = ?");
        $stmt->execute([$fullname, $user['id']]);

        // Update password if provided
        if (!empty($newPassword)) {
            $hashedPassword = hashPassword($newPassword);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);
        }

        $message = 'Cập nhật thông tin thành công!';
        $user['fullname'] = $fullname; // Update local variable
        $_SESSION['fullname'] = $fullname;
    } else {
        $message = implode('<br>', $errors);
    }
}
?>

<?php include 'header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Chỉnh sửa thông tin cá nhân</h3>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Họ tên</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                    </div>
                    <hr>
                    <h5>Đổi mật khẩu (để trống nếu không muốn đổi)</h5>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                        <div class="form-text">Ít nhất 6 ký tự.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
