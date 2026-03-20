<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Kiểm tra tài khoản có bị khóa không
        if ($user['status'] === 'locked') {
            $error = "Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên!";
        } else {
            // Kiểm tra password
            if (password_verify($password, $user['password'])) {
                // Lưu thông tin vào session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['avatar'] = $user['avatar'];
                
                // Chuyển hướng theo role
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } elseif ($user['role'] === 'teacher') {
                    header("Location: teacher.php");
                } else {
                    header("Location: student.php");
                }
                exit;
            } else {
                $error = "Sai tài khoản hoặc mật khẩu!";
            }
        }
    } else {
        $error = "Sai tài khoản hoặc mật khẩu!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống quản lý điểm</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="login-icon">🎓</div>
                <h2>Đăng nhập hệ thống</h2>
                <p>Hệ thống quản lý điểm sinh viên</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">⚠️</span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="username">
                        <span class="label-icon">👤</span>
                        Tài khoản
                    </label>
                    <input type="text" id="username" name="username" placeholder="Nhập MSSV hoặc MSGV" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <span class="label-icon">🔒</span>
                        Mật khẩu
                    </label>
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <span>Đăng nhập</span>
                    <span class="btn-arrow">→</span>
                </button>
            </form>
            
            <div class="login-footer">
                <div class="divider">
                    <span>Tài khoản mặc định</span>
                </div>
                <div class="default-accounts">
                    <div class="account-item">
                        <span class="account-role">👨‍💼 Admin</span>
                        <span class="account-info">admin / 123456</span>
                    </div>
                    <div class="account-item">
                        <span class="account-role">👨‍🏫 Giảng viên</span>
                        <span class="account-info">GV001 / 123456</span>
                    </div>
                    <div class="account-item">
                        <span class="account-role">👨‍🎓 Sinh viên</span>
                        <span class="account-info">SV001 / 123456</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
