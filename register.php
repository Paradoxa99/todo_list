<?php
require_once 'functions.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');

    // Validation
    $errors = [];
    if (!validateUsername($username)) {
        $errors[] = 'Tên đăng nhập phải từ 3-50 ký tự, chỉ chứa chữ cái, số và dấu gạch dưới.';
    }
    if (!validateEmail($email)) {
        $errors[] = 'Email không hợp lệ.';
    }
    if (!validatePassword($password)) {
        $errors[] = 'Mật khẩu phải ít nhất 6 ký tự.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Mật khẩu xác nhận không khớp.';
    }
    if (!validateFullname($fullname)) {
        $errors[] = 'Họ tên không được để trống và tối đa 100 ký tự.';
    }

    // Check if username or email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Tên đăng nhập hoặc email đã tồn tại.';
        }
    }

    if (empty($errors)) {
        // Register user
        $hashedPassword = hashPassword($password);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, fullname) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashedPassword, $fullname])) {
            $message = 'Đăng ký thành công! <a href="login.php">Đăng nhập</a>';
        } else {
            $message = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
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
                <h3 class="card-title">Đăng ký tài khoản</h3>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                        <div class="form-text">3-50 ký tự, chỉ chứa chữ cái, số và dấu gạch dưới.</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Họ tên</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Ít nhất 6 ký tự.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Đăng ký</button>
                </form>
                <p class="mt-3">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
