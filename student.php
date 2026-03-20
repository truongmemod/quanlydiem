<?php
require_once 'functions.php';
checkRole('student');

$student_id = $_SESSION['user_id'];
$student_info = getUserById($student_id, $conn);

$page = $_GET['page'] ?? 'grades';

// Lấy điểm của sinh viên
$grades_sql = "SELECT g.*, s.name as subject_name, s.credits, s.percent_cc, s.percent_mid, s.percent_final, c.semester
               FROM grades g
               JOIN subjects s ON g.subject_id = s.subject_id
               LEFT JOIN classes c ON g.class_id = c.id
               WHERE g.student_id = ?
               ORDER BY c.semester DESC, s.name";
$stmt = $conn->prepare($grades_sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$grades = $stmt->get_result();

// Tính GPA
$gpa_cumulative = calculateCumulativeGPA($student_id, $conn);
$credits_passed = countCredits($student_id, true, $conn);
$credits_failed = countCredits($student_id, false, $conn);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinh viên - Hệ thống quản lý điểm</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="student-page">
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <span class="logo-icon">👨‍🎓</span>
                <span class="logo-text">SINH VIÊN</span>
            </div>
            <div class="user-info">
                <img src="<?= getAvatarPath($_SESSION['avatar']) ?>" alt="Avatar" class="user-avatar">
                <div class="user-details">
                    <div class="user-name"><?= e($_SESSION['full_name']) ?></div>
                    <div class="user-role"><?= e($_SESSION['username']) ?></div>
                </div>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="?page=grades" class="nav-item <?= $page === 'grades' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Xem điểm</span>
            </a>
            <a href="?page=profile" class="nav-item <?= $page === 'profile' ? 'active' : '' ?>">
                <span class="nav-icon">👤</span>
                <span class="nav-text">Thông tin cá nhân</span>
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
                <?php echo $page === 'profile' ? '👤 Thông tin cá nhân' : '📊 Kết quả học tập'; ?>
            </h1>
            <div class="topbar-actions">
                <span class="datetime"><?= date('d/m/Y H:i') ?></span>
            </div>
        </div>
        
        <div class="content-wrapper">
            <?php if ($page === 'profile'): ?>
                <!-- Profile -->
                <div class="student-profile-card">
                    <div class="profile-cover" style="background-image: url('<?= getCoverPath($student_info['cover']) ?>');"></div>
                    <div class="profile-info">
                        <img src="<?= getAvatarPath($student_info['avatar']) ?>" class="profile-avatar" alt="Avatar">
                        <div class="profile-details">
                            <h2><?= e($student_info['full_name']) ?></h2>
                            <p class="profile-username"><?= e($student_info['username']) ?></p>
                        </div>
                    </div>
                    <div class="profile-data">
                        <div class="data-row">
                            <span class="data-label">📧 Email:</span>
                            <span class="data-value"><?= e($student_info['email']) ?></span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">🎂 Ngày sinh:</span>
                            <span class="data-value"><?= formatDate($student_info['dob']) ?></span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">🏫 Khoa:</span>
                            <span class="data-value"><?= e($student_info['faculty']) ?></span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">👥 Lớp:</span>
                            <span class="data-value"><?= e($student_info['class_name']) ?></span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">🏠 Quê quán:</span>
                            <span class="data-value"><?= e($student_info['hometown']) ?></span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Grades -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">🎯</div>
                        <div class="stat-info">
                            <div class="stat-value"><?= number_format($gpa_cumulative, 2) ?></div>
                            <div class="stat-label">GPA tích lũy</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">✅</div>
                        <div class="stat-info">
                            <div class="stat-value"><?= $credits_passed ?></div>
                            <div class="stat-label">Tín chỉ đạt</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">❌</div>
                        <div class="stat-info">
                            <div class="stat-value"><?= $credits_failed ?></div>
                            <div class="stat-label">Tín chỉ chưa đạt</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">📚</div>
                        <div class="stat-info">
                            <div class="stat-value"><?= $credits_passed + $credits_failed ?></div>
                            <div class="stat-label">Tổng tín chỉ</div>
                        </div>
                    </div>
                </div>
                
                <div class="data-card">
                    <h3>📊 Bảng điểm chi tiết</h3>
                    <?php if ($grades->num_rows > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Mã môn</th>
                                    <th>Tên môn</th>
                                    <th>TC</th>
                                    <th>Học kỳ</th>
                                    <th>CC</th>
                                    <th>GK</th>
                                    <th>CK</th>
                                    <th>Tổng</th>
                                    <th>Chữ</th>
                                    <th>Hệ 4</th>
                                    <th>Kết quả</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($grade = $grades->fetch_assoc()): 
                                    $total = calculateTotalScore(
                                        $grade['score_cc'],
                                        $grade['score_mid'],
                                        $grade['score_final'],
                                        $grade['percent_cc'],
                                        $grade['percent_mid'],
                                        $grade['percent_final']
                                    );
                                    $passed = isPassed($total);
                                ?>
                                    <tr>
                                        <td><strong><?= e($grade['subject_id']) ?></strong></td>
                                        <td><?= e($grade['subject_name']) ?></td>
                                        <td><?= $grade['credits'] ?></td>
                                        <td><span class="badge badge-info"><?= e($grade['semester']) ?></span></td>
                                        <td><?= $grade['score_cc'] ?? '-' ?></td>
                                        <td><?= $grade['score_mid'] ?? '-' ?></td>
                                        <td><?= $grade['score_final'] ?? '-' ?></td>
                                        <td><strong><?= $total ?? '-' ?></strong></td>
                                        <td><span class="badge badge-primary"><?= scoreToChar($total) ?></span></td>
                                        <td><?= scoreTo4($total) ?></td>
                                        <td>
                                            <?php if ($passed): ?>
                                                <span class="badge badge-success">Đạt</span>
                                            <?php elseif ($total !== null): ?>
                                                <span class="badge badge-danger">Chưa đạt</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Chưa có</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-data">Chưa có điểm</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="assets/script.js"></script>
</body>
</html>
