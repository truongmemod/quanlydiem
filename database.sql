-- =====================================================
-- HỆ THỐNG QUẢN LÝ ĐIỂM SINH VIÊN - DATABASE
-- =====================================================

CREATE DATABASE IF NOT EXISTS quan_ly_diem
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE quan_ly_diem;

-- =====================================================
-- BẢNG USERS (Quản lý tài khoản)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin','teacher','student') NOT NULL,
    class_name VARCHAR(50),
    faculty VARCHAR(100),
    email VARCHAR(100),
    dob DATE,
    hometown VARCHAR(200),
    avatar VARCHAR(255) DEFAULT 'default.jpg',
    cover VARCHAR(255) DEFAULT 'default-cover.jpg',
    status ENUM('active','locked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_faculty (faculty),
    INDEX idx_username (username)
);

-- =====================================================
-- BẢNG SUBJECTS (Môn học)
-- =====================================================
CREATE TABLE subjects (
    subject_id VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    credits INT NOT NULL,
    percent_cc INT DEFAULT 10,
    percent_mid INT DEFAULT 30,
    percent_final INT DEFAULT 60,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_credits (credits)
);

-- =====================================================
-- BẢNG CLASSES (Lớp học phần - Phân công giảng dạy)
-- =====================================================
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id VARCHAR(20) NOT NULL,
    teacher_id INT NOT NULL,
    semester VARCHAR(50) NOT NULL,
    class_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_semester (semester),
    INDEX idx_teacher (teacher_id),
    INDEX idx_subject (subject_id)
);

-- =====================================================
-- BẢNG GRADES (Điểm số)
-- =====================================================
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id VARCHAR(20) NOT NULL,
    class_id INT,
    score_cc FLOAT DEFAULT NULL,
    score_mid FLOAT DEFAULT NULL,
    score_final FLOAT DEFAULT NULL,
    is_locked TINYINT DEFAULT 0,
    semester VARCHAR(50),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_student_subject (student_id, subject_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    INDEX idx_locked (is_locked),
    INDEX idx_student (student_id),
    INDEX idx_semester (semester)
);

-- =====================================================
-- DỮ LIỆU MẪU
-- =====================================================

-- Tài khoản Admin mặc định (password: 123456)
INSERT INTO users (username, password, full_name, role, email, status) 
VALUES ('admin', '$2y$10$6TdAzkXJfUzN6SGsaWQjNuQr0tnklU2tZAsP/2ETkNNyAr4xuoL/y', 'Administrator', 'admin', 'admin@university.edu.vn', 'active');

-- Môn học mẫu
INSERT INTO subjects (subject_id, name, credits, percent_cc, percent_mid, percent_final) VALUES
('MATH101', 'Toán cao cấp 1', 3, 10, 30, 60),
('MATH102', 'Toán cao cấp 2', 3, 10, 30, 60),
('PHYS101', 'Vật lý đại cương 1', 3, 10, 30, 60),
('PHYS102', 'Vật lý đại cương 2', 3, 10, 30, 60),
('PROG101', 'Lập trình căn bản', 4, 20, 30, 50),
('PROG102', 'Lập trình hướng đối tượng', 4, 20, 30, 50),
('ENG101', 'Tiếng Anh 1', 3, 10, 20, 70),
('ENG102', 'Tiếng Anh 2', 3, 10, 20, 70),
('CHEM101', 'Hóa học đại cương', 3, 10, 30, 60),
('DATABASE', 'Cơ sở dữ liệu', 4, 15, 25, 60),
('WEB101', 'Lập trình Web', 4, 15, 25, 60),
('NETWORK', 'Mạng máy tính', 3, 10, 30, 60);

-- Giảng viên mẫu (password: 123456)
INSERT INTO users (username, password, full_name, role, faculty, email, dob, status) VALUES
('GV001', '$2y$10$6TdAzkXJfUzN6SGsaWQjNuQr0tnklU2tZAsP/2ETkNNyAr4xuoL/y', 'Nguyễn Văn An', 'teacher', 'Khoa Công nghệ thông tin', 'nguyenvanan@university.edu.vn', '1980-05-15', 'active'),
('GV002', '$2y$10$6TdAzkXJfUzN6SGsaWQjNuQr0tnklU2tZAsP/2ETkNNyAr4xuoL/y', 'Trần Thị Bình', 'teacher', 'Khoa Toán - Tin', 'tranthib inh@university.edu.vn', '1985-08-20', 'active'),
('GV003', '$2y$10$6TdAzkXJfUzN6SGsaWQjNuQr0tnklU2tZAsP/2ETkNNyAr4xuoL/y', 'Lê Văn Cường', 'teacher', 'Khoa Công nghệ thông tin', 'levancuong@university.edu.vn', '1982-03-10', 'active'),
('GV004', '$2y$10$6TdAzkXJfUzN6SGsaWQjNuQr0tnklU2tZAsP/2ETkNNyAr4xuoL/y', 'Phạm Thị Dung', 'teacher', 'Khoa Ngoại ngữ', 'phamthidung@university.edu.vn', '1987-11-25', 'active');

-- Sinh viên mẫu (password: 123456)
INSERT INTO users (username, password, full_name, role, class_name, faculty, email, dob, hometown, status) VALUES
('SV001', '$2y$10$6TdAzkXJfUzN6SGsaWQjNuQr0tnklU2tZAsP/2ETkNNyAr4xuoL/y', 'Hoàng Văn Minh', 'student', 'CT01', 'Khoa Công nghệ thông tin', 'hoangvanminh@student.edu.vn', '2005-03-10', 'Hà Nội', 'active'),
('SV002', '$2y$10$6TdAzkXJfUzN6SGsaWQjNuQr0tnklU2tZAsP/2ETkNNyAr4xuoL/y', 'Nguyễn Thị Hoa', 'student', 'CT01', 'Khoa Công nghệ thông tin', 'nguyenthihoa@student.edu.vn', '2005-07-22', 'Hồ Chí Minh', 'active'),
('SV003', '$2y$10$6TdAzkXJfUzN6SGsaWQjNuQr0tnklU2tZAsP/2ETkNNyAr4xuoL/y', 'Trần Văn Nam', 'student', 'CT01', 'Khoa Công nghệ thông tin', 'tranvannam@student.edu.vn', '2005-05-15', 'Đà Nẵng', 'active'),
('SV004', '$2y$10$6TdAzkXJfUzN6SGsaWQjNuQr0tnklU2tZAsP/2ETkNNyAr4xuoL/y', 'Lê Thị Lan', 'student', 'CT02', 'Khoa Công nghệ thông tin', 'lethilan@student.edu.vn', '2005-09-08', 'Hải Phòng', 'active'),
('SV005', '$2y$10$6TdAzkXJfUzN6SGsaWQjNuQr0tnklU2tZAsP/2ETkNNyAr4xuoL/y', 'Phạm Văn Tùng', 'student', 'CT02', 'Khoa Công nghệ thông tin', 'phamvantung@student.edu.vn', '2005-12-20', 'Cần Thơ', 'active');

-- Phân công giảng dạy mẫu
INSERT INTO classes (subject_id, teacher_id, semester, class_name) VALUES
('MATH101', 2, 'HK1 2024-2025', 'CT01'),
('PROG101', 1, 'HK1 2024-2025', 'CT01'),
('ENG101', 4, 'HK1 2024-2025', 'CT01'),
('PHYS101', 3, 'HK1 2024-2025', 'CT02'),
('DATABASE', 1, 'HK2 2024-2025', 'CT01');

-- Điểm mẫu
INSERT INTO grades (student_id, subject_id, class_id, score_cc, score_mid, score_final, semester, is_locked) VALUES
(2, 'MATH101', 1, 8.5, 7.5, 8.0, 'HK1 2024-2025', 1),
(2, 'PROG101', 2, 9.0, 8.5, 9.0, 'HK1 2024-2025', 1),
(2, 'ENG101', 3, 7.0, 7.5, 7.0, 'HK1 2024-2025', 0),
(3, 'MATH101', 1, 6.5, 7.0, 6.5, 'HK1 2024-2025', 1),
(3, 'PROG101', 2, 8.0, 7.5, 8.0, 'HK1 2024-2025', 1);
