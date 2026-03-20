<?php
require_once 'config.php';

// Nếu đã đăng nhập, chuyển đến trang tương ứng
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } elseif ($_SESSION['role'] === 'teacher') {
        header("Location: teacher.php");
    } else {
        header("Location: student.php");
    }
} else {
    header("Location: login.php");
}
exit;
