<?php
// =====================================================
// CONFIG - CẤU HÌNH HỆ THỐNG
// =====================================================

// Cấu hình database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'quan_ly_diem');

// Kết nối database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("❌ Kết nối database thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cấu hình timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Bật hiển thị lỗi (chỉ dùng khi development)
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
