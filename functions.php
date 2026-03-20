<?php
// =====================================================
// FUNCTIONS - HỆ THỐNG QUẢN LÝ ĐIỂM
// =====================================================

require_once 'config.php';

// =====================================================
// KIỂM TRA ĐĂNG NHẬP VÀ PHÂN QUYỀN
// =====================================================

/**
 * Kiểm tra user đã đăng nhập chưa
 */
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Kiểm tra quyền truy cập
 */
function checkRole($required_role) {
    checkLogin();
    if ($_SESSION['role'] !== $required_role) {
        die("❌ Bạn không có quyền truy cập trang này!");
    }
}

/**
 * Kiểm tra nhiều quyền
 */
function checkRoles($roles = []) {
    checkLogin();
    if (!in_array($_SESSION['role'], $roles)) {
        die("❌ Bạn không có quyền truy cập trang này!");
    }
}

// =====================================================
// XỬ LÝ ĐIỂM SỐ
// =====================================================

/**
 * Tính điểm tổng hệ 10
 */
function calculateTotalScore($cc, $mid, $final, $percent_cc, $percent_mid, $percent_final) {
    if ($cc === null || $mid === null || $final === null) {
        return null;
    }
    return round(($cc * $percent_cc + $mid * $percent_mid + $final * $percent_final) / 100, 2);
}

/**
 * Quy đổi điểm hệ 10 sang điểm chữ
 */
function scoreToChar($score) {
    if ($score === null) return '-';
    if ($score >= 8.5) return 'A';
    if ($score >= 8.0) return 'B+';
    if ($score >= 7.0) return 'B';
    if ($score >= 6.5) return 'C+';
    if ($score >= 5.5) return 'C';
    if ($score >= 5.0) return 'D+';
    if ($score >= 4.0) return 'D';
    return 'F';
}

/**
 * Quy đổi điểm hệ 10 sang hệ 4
 */
function scoreTo4($score) {
    if ($score === null) return 0;
    if ($score >= 8.5) return 4.0;
    if ($score >= 8.0) return 3.5;
    if ($score >= 7.0) return 3.0;
    if ($score >= 6.5) return 2.5;
    if ($score >= 5.5) return 2.0;
    if ($score >= 5.0) return 1.5;
    if ($score >= 4.0) return 1.0;
    return 0.0;
}

/**
 * Kiểm tra điểm có đạt không
 */
function isPassed($score) {
    return $score !== null && $score >= 4.0;
}

// =====================================================
// QUẢN LÝ TÀI KHOẢN
// =====================================================

/**
 * Tạo MSSV/MSGV tự động
 */
function generateUserCode($role, $conn) {
    $prefix = ($role === 'student') ? 'SV' : 'GV';
    
    $sql = "SELECT username FROM users WHERE role = ? AND username LIKE ? ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $like_pattern = $prefix . '%';
    $stmt->bind_param("ss", $role, $like_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $last_code = $row['username'];
        $number = intval(substr($last_code, 2)) + 1;
    } else {
        $number = 1;
    }
    
    return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
}

/**
 * Upload ảnh
 */
function uploadImage($file, $folder = 'avatar') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return null;
    }
    
    // Tạo thư mục nếu chưa tồn tại
    $upload_dir = "assets/uploads/$folder/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = time() . '_' . uniqid() . '.' . $ext;
    $path = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $path)) {
        return $filename;
    }
    
    return null;
}

/**
 * Xóa ảnh cũ
 */
function deleteImage($filename, $folder = 'avatar') {
    if ($filename && !in_array($filename, ['default.jpg', 'default-cover.jpg'])) {
        $path = "assets/uploads/$folder/" . $filename;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}

// =====================================================
// THỐNG KÊ
// =====================================================

/**
 * Đếm số lượng theo điều kiện
 */
function countRecords($table, $where = '', $conn) {
    $sql = "SELECT COUNT(*) as total FROM $table";
    if ($where) {
        $sql .= " WHERE $where";
    }
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'];
}

/**
 * Tính GPA từng học kỳ
 */
function calculateSemesterGPA($student_id, $semester, $conn) {
    $sql = "SELECT g.*, s.credits, s.percent_cc, s.percent_mid, s.percent_final
            FROM grades g
            JOIN subjects s ON g.subject_id = s.subject_id
            WHERE g.student_id = ? AND g.semester = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $student_id, $semester);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total_points = 0;
    $total_credits = 0;
    
    while ($row = $result->fetch_assoc()) {
        $score = calculateTotalScore(
            $row['score_cc'], 
            $row['score_mid'], 
            $row['score_final'],
            $row['percent_cc'],
            $row['percent_mid'],
            $row['percent_final']
        );
        
        if ($score !== null) {
            $score_4 = scoreTo4($score);
            $total_points += $score_4 * $row['credits'];
            $total_credits += $row['credits'];
        }
    }
    
    return $total_credits > 0 ? round($total_points / $total_credits, 2) : 0;
}

/**
 * Tính GPA tích lũy
 */
function calculateCumulativeGPA($student_id, $conn) {
    $sql = "SELECT g.*, s.credits, s.percent_cc, s.percent_mid, s.percent_final
            FROM grades g
            JOIN subjects s ON g.subject_id = s.subject_id
            WHERE g.student_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total_points = 0;
    $total_credits = 0;
    
    while ($row = $result->fetch_assoc()) {
        $score = calculateTotalScore(
            $row['score_cc'], 
            $row['score_mid'], 
            $row['score_final'],
            $row['percent_cc'],
            $row['percent_mid'],
            $row['percent_final']
        );
        
        if ($score !== null) {
            $score_4 = scoreTo4($score);
            $total_points += $score_4 * $row['credits'];
            $total_credits += $row['credits'];
        }
    }
    
    return $total_credits > 0 ? round($total_points / $total_credits, 2) : 0;
}

/**
 * Đếm tín chỉ đạt/chưa đạt
 */
function countCredits($student_id, $passed = true, $conn) {
    $sql = "SELECT g.*, s.credits, s.percent_cc, s.percent_mid, s.percent_final
            FROM grades g
            JOIN subjects s ON g.subject_id = s.subject_id
            WHERE g.student_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $score = calculateTotalScore(
            $row['score_cc'], 
            $row['score_mid'], 
            $row['score_final'],
            $row['percent_cc'],
            $row['percent_mid'],
            $row['percent_final']
        );
        
        $is_passed = isPassed($score);
        
        if (($passed && $is_passed) || (!$passed && !$is_passed && $score !== null)) {
            $total += $row['credits'];
        }
    }
    
    return $total;
}

/**
 * Lấy danh sách học kỳ
 */
function getSemesters($conn) {
    $sql = "SELECT DISTINCT semester FROM classes ORDER BY semester DESC";
    $result = $conn->query($sql);
    $semesters = [];
    while ($row = $result->fetch_assoc()) {
        $semesters[] = $row['semester'];
    }
    return $semesters;
}

/**
 * Lấy danh sách khoa
 */
function getFaculties($conn) {
    $sql = "SELECT DISTINCT faculty FROM users WHERE faculty IS NOT NULL AND faculty != '' ORDER BY faculty";
    $result = $conn->query($sql);
    $faculties = [];
    while ($row = $result->fetch_assoc()) {
        $faculties[] = $row['faculty'];
    }
    return $faculties;
}

/**
 * Lấy danh sách lớp
 */
function getClasses($conn) {
    $sql = "SELECT DISTINCT class_name FROM users WHERE class_name IS NOT NULL AND class_name != '' ORDER BY class_name";
    $result = $conn->query($sql);
    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row['class_name'];
    }
    return $classes;
}

// =====================================================
// FORMAT DỮ LIỆU
// =====================================================

/**
 * Format ngày tháng
 */
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

/**
 * Escape HTML
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Get avatar path
 */
function getAvatarPath($filename) {
    if (!$filename || $filename === 'default.jpg') {
        return 'https://ui-avatars.com/api/?name=User&background=4F46E5&color=fff&size=200';
    }
    return 'assets/uploads/avatar/' . $filename;
}

/**
 * Get cover path
 */
function getCoverPath($filename) {
    if (!$filename || $filename === 'default-cover.jpg') {
        return 'https://images.unsplash.com/photo-1557683316-973673baf926?w=1200&h=400&fit=crop';
    }
    return 'assets/uploads/cover/' . $filename;
}

/**
 * Lấy thông tin user theo ID
 */
function getUserById($id, $conn) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Lấy thông tin môn học theo ID
 */
function getSubjectById($subject_id, $conn) {
    $sql = "SELECT * FROM subjects WHERE subject_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Lấy thông tin lớp học phần theo ID
 */
function getClassById($id, $conn) {
    $sql = "SELECT c.*, s.name as subject_name, u.full_name as teacher_name 
            FROM classes c 
            JOIN subjects s ON c.subject_id = s.subject_id 
            JOIN users u ON c.teacher_id = u.id 
            WHERE c.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
