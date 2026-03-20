<?php
require_once 'functions.php';
checkRole('teacher');

$page = $_GET['page'] ?? 'classes';
$teacher_id = $_SESSION['user_id'];

// Lấy danh sách lớp của giảng viên
$classes_sql = "SELECT c.*, s.name as subject_name, s.credits
                FROM classes c
                JOIN subjects s ON c.subject_id = s.subject_id
                WHERE c.teacher_id = ?
                ORDER BY c.semester DESC";
$stmt = $conn->prepare($classes_sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$my_classes = $stmt->get_result();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giảng viên - Hệ thống quản lý điểm</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="teacher-page">
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <span class="logo-icon">👨‍🏫</span>
                <span class="logo-text">GIẢNG VIÊN</span>
            </div>
            <div class="user-info">
                <img src="<?= getAvatarPath($_SESSION['avatar']) ?>" alt="Avatar" class="user-avatar">
                <div class="user-details">
                    <div class="user-name"><?= e($_SESSION['full_name']) ?></div>
                    <div class="user-role">Giảng viên</div>
                </div>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="?page=classes" class="nav-item <?= $page === 'classes' ? 'active' : '' ?>">
                <span class="nav-icon">📚</span>
                <span class="nav-text">Lớp giảng dạy</span>
            </a>
            <a href="?page=grades" class="nav-item <?= $page === 'grades' ? 'active' : '' ?>">
                <span class="nav-icon">✍️</span>
                <span class="nav-text">Nhập điểm</span>
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
                echo $page === 'classes' ? '📚 Lớp giảng dạy' : '✍️ Nhập điểm';
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
            if ($page === 'grades') {
                include 'teacher_grades.php';
            } else {
            ?>
                <div class="data-card">
                    <h3>📋 Danh sách lớp đang giảng dạy</h3>
                    <?php if ($my_classes->num_rows > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Mã môn</th>
                                    <th>Tên môn</th>
                                    <th>Học kỳ</th>
                                    <th>Lớp</th>
                                    <th>Số TC</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($class = $my_classes->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?= e($class['subject_id']) ?></strong></td>
                                        <td><?= e($class['subject_name']) ?></td>
                                        <td><span class="badge badge-info"><?= e($class['semester']) ?></span></td>
                                        <td><span class="badge badge-success"><?= e($class['class_name']) ?></span></td>
                                        <td><?= $class['credits'] ?></td>
                                        <td>
                                            <a href="?page=grades&class_id=<?= $class['id'] ?>" class="btn-icon btn-primary">✍️ Nhập điểm</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-data">Bạn chưa được phân công giảng dạy lớp nào</p>
                    <?php endif; ?>
                </div>
            <?php } ?>
        </div>
    </div>
    
    <script src="assets/script.js"></script>
</body>
</html>
