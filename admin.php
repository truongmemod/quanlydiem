<?php
require_once 'functions.php';
checkRole('admin');

$page = $_GET['page'] ?? 'dashboard';

// Lấy thống kê
$total_students = countRecords('users', "role='student'", $conn);
$total_teachers = countRecords('users', "role='teacher'", $conn);
$total_subjects = countRecords('subjects', '', $conn);
$total_classes = countRecords('classes', '', $conn);

// Lấy flash message
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Hệ thống quản lý điểm</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="admin-page">
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <span class="logo-icon">🎓</span>
                <span class="logo-text">QUẢN TRỊ</span>
            </div>
            <div class="user-info">
                <img src="<?= getAvatarPath($_SESSION['avatar']) ?>" alt="Avatar" class="user-avatar">
                <div class="user-details">
                    <div class="user-name"><?= e($_SESSION['full_name']) ?></div>
                    <div class="user-role">Quản trị viên</div>
                </div>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="?page=dashboard" class="nav-item <?= $page === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Tổng quan</span>
            </a>
            <a href="?page=students" class="nav-item <?= $page === 'students' ? 'active' : '' ?>">
                <span class="nav-icon">👨‍🎓</span>
                <span class="nav-text">Quản lý sinh viên</span>
            </a>
            <a href="?page=teachers" class="nav-item <?= $page === 'teachers' ? 'active' : '' ?>">
                <span class="nav-icon">👨‍🏫</span>
                <span class="nav-text">Quản lý giảng viên</span>
            </a>
            <a href="?page=subjects" class="nav-item <?= $page === 'subjects' ? 'active' : '' ?>">
                <span class="nav-icon">📚</span>
                <span class="nav-text">Quản lý môn học</span>
            </a>
            <a href="?page=classes" class="nav-item <?= $page === 'classes' ? 'active' : '' ?>">
                <span class="nav-icon">🏫</span>
                <span class="nav-text">Phân công giảng dạy</span>
            </a>
            <a href="logout.php" class="nav-item nav-logout">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Đăng xuất</span>
            </a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="topbar">
            <h1 class="page-title">
                <?php
                $titles = [
                    'dashboard' => '📊 Tổng quan hệ thống',
                    'students' => '👨‍🎓 Quản lý sinh viên',
                    'teachers' => '👨‍🏫 Quản lý giảng viên',
                    'subjects' => '📚 Quản lý môn học',
                    'classes' => '🏫 Phân công giảng dạy'
                ];
                echo $titles[$page] ?? 'Admin Dashboard';
                ?>
            </h1>
            <div class="topbar-actions">
                <span class="datetime"><?= date('d/m/Y H:i') ?></span>
            </div>
        </div>
        
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <span class="alert-icon"><?= $flash['type'] === 'success' ? '✅' : '⚠️' ?></span>
                <span><?= e($flash['message']) ?></span>
            </div>
        <?php endif; ?>
        
        <div class="content-wrapper">
            <?php
            switch ($page) {
                case 'dashboard':
                    include 'admin_dashboard.php';
                    break;
                case 'students':
                    include 'admin_students.php';
                    break;
                case 'teachers':
                    include 'admin_teachers.php';
                    break;
                case 'subjects':
                    include 'admin_subjects.php';
                    break;
                case 'classes':
                    include 'admin_classes.php';
                    break;
                default:
                    include 'admin_dashboard.php';
            }
            ?>
        </div>
    </div>
    
    <script src="assets/script.js"></script>
</body>
</html>
