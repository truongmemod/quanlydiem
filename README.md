# 🎓 HỆ THỐNG QUẢN LÝ ĐIỂM SINH VIÊN

Hệ thống quản lý điểm sinh viên hoàn chỉnh với đầy đủ chức năng cho Admin, Giảng viên và Sinh viên.

## ✨ TÍNH NĂNG CHÍNH

### 🔐 Chức năng chung
- ✅ Đăng nhập theo vai trò (Admin / Giảng viên / Sinh viên)
- ✅ Phân quyền rõ ràng theo tài khoản
- ✅ Đăng xuất
- ✅ Khóa / mở khóa tài khoản
- ✅ Lưu session đăng nhập
- ✅ Kiểm tra truy cập trái phép

### 👨‍💼 Admin
#### Quản lý tài khoản
- ✅ Tạo tài khoản sinh viên (MSSV tự tăng)
- ✅ Tạo tài khoản giảng viên (MSGV tự tăng)
- ✅ Thông tin đầy đủ: MSSV/MSGV, họ tên, ngày sinh, email, khoa, lớp, ảnh đại diện, ảnh nền
- ✅ Sửa thông tin sinh viên / giảng viên
- ✅ Xóa tài khoản
- ✅ Khóa / mở khóa tài khoản

#### Quản lý môn học
- ✅ Thêm / sửa / xóa môn học
- ✅ Thiết lập số tín chỉ, % điểm chuyên cần, giữa kỳ, cuối kỳ

#### Phân công giảng dạy
- ✅ Gán giảng viên cho môn học
- ✅ Chọn học kỳ, lớp học phần
- ✅ Xem danh sách môn giảng viên đang dạy

#### Thống kê
- ✅ Tổng số sinh viên, giảng viên, môn học, lớp học phần
- ✅ Xem danh sách sinh viên theo khoa / lớp
- ✅ Hoạt động gần đây

### 👨‍🏫 Giảng viên
- ✅ Xem danh sách môn được phân công
- ✅ Xem danh sách sinh viên từng môn
- ✅ Nhập điểm chuyên cần, giữa kỳ, cuối kỳ
- ✅ Tự động tính điểm tổng (hệ 10), quy đổi điểm chữ (A-F), quy đổi hệ 4
- ✅ Sửa điểm (khi chưa khóa)
- ✅ Khóa bảng điểm

### 👨‍🎓 Sinh viên
- ✅ Xem điểm từng môn (thành phần, tổng, chữ, hệ 4)
- ✅ Xem điểm theo học kỳ, năm học
- ✅ Tính GPA từng học kỳ và tích lũy
- ✅ Tổng số tín chỉ đạt / chưa đạt
- ✅ Xem hồ sơ cá nhân (ảnh đại diện + ảnh nền)
- ✅ Không được sửa thông tin (chỉ admin sửa)

## 📋 YÊU CẦU HỆ THỐNG

- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Apache/Nginx
- XAMPP/WAMP/LAMP (khuyến nghị)

## 🚀 HƯỚNG DẪN CÀI ĐẶT

### Bước 1: Chuẩn bị

1. Tải và cài đặt XAMPP: https://www.apachefriends.org/
2. Khởi động Apache và MySQL trong XAMPP Control Panel

### Bước 2: Cài đặt database

1. Mở trình duyệt, truy cập: `http://localhost/phpmyadmin`
2. Click vào tab "SQL"
3. Copy toàn bộ nội dung file `database.sql` và paste vào
4. Click "Go" để thực thi

### Bước 3: Cài đặt source code

1. Copy toàn bộ thư mục dự án vào `C:\xampp\htdocs\quan_ly_diem`
2. Mở file `config.php` và kiểm tra cấu hình database:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'quan_ly_diem');
   ```

### Bước 4: Chạy ứng dụng

1. Mở trình duyệt
2. Truy cập: `http://localhost/quan_ly_diem`
3. Đăng nhập với tài khoản mặc định

## 🔑 TÀI KHOẢN MẶC ĐỊNH

| Vai trò | Tài khoản | Mật khẩu |
|---------|-----------|----------|
| Admin | admin | 123456 |
| Giảng viên | GV001 | 123456 |
| Sinh viên | SV001 | 123456 |

## 📁 CẤU TRÚC THƯ MỤC

```
quan_ly_diem/
├── assets/
│   ├── style.css          # CSS chính
│   ├── script.js          # JavaScript
│   └── uploads/           # Thư mục lưu ảnh
│       ├── avatar/        # Ảnh đại diện
│       └── cover/         # Ảnh nền
├── config.php             # Cấu hình database
├── functions.php          # Hàm tiện ích
├── login.php             # Trang đăng nhập
├── logout.php            # Đăng xuất
├── index.php             # Trang chủ
├── admin.php             # Trang admin chính
├── admin_dashboard.php   # Dashboard admin
├── admin_students.php    # Quản lý sinh viên
├── admin_teachers.php    # Quản lý giảng viên
├── admin_subjects.php    # Quản lý môn học
├── admin_classes.php     # Phân công giảng dạy
├── teacher.php           # Trang giảng viên chính
├── teacher_grades.php    # Nhập điểm
├── student.php           # Trang sinh viên
├── database.sql          # File cơ sở dữ liệu
└── README.md             # File hướng dẫn
```

## 🎨 GIAO DIỆN

- **Admin**: Giao diện quản trị với sidebar, menu đầy đủ
- **Giảng viên**: Giao diện nhập điểm, quản lý lớp
- **Sinh viên**: Giao diện xem điểm, hồ sơ cá nhân
- **Responsive**: Tương thích với mobile và tablet
- **Modal popup**: Xem chi tiết, thêm/sửa thông tin

## 🔧 TÍNH NĂNG KỸ THUẬT

- **Database**: MySQL với quan hệ khóa ngoại đầy đủ
- **Session**: Quản lý phiên đăng nhập an toàn
- **Security**: Password hash, prepared statements (SQL injection prevention)
- **File upload**: Hỗ trợ upload ảnh đại diện và ảnh nền
- **Auto-increment**: MSSV/MSGV tự tăng
- **Validation**: Kiểm tra dữ liệu đầu vào
- **Flash messages**: Thông báo thành công/lỗi

## 📊 DATABASE

### Bảng chính:
- `users`: Quản lý tài khoản (admin, teacher, student)
- `subjects`: Quản lý môn học
- `classes`: Lớp học phần (phân công giảng dạy)
- `grades`: Điểm số

### Ràng buộc:
- MSSV/MSGV tự tăng và unique
- Mỗi sinh viên chỉ có 1 dòng điểm/1 môn
- Khóa ngoại giữa các bảng
- Không nhập điểm cho môn chưa phân công

## ⚠️ LƯU Ý

1. **Khóa bảng điểm**: Sau khi khóa, giảng viên không thể sửa điểm
2. **Upload ảnh**: Chỉ chấp nhận file JPG, PNG, GIF, WEBP
3. **Quyền truy cập**: Hệ thống kiểm tra quyền trước mỗi thao tác
4. **Mật khẩu mặc định**: Nên đổi ngay sau lần đăng nhập đầu tiên

## 🐛 TROUBLESHOOTING

### Lỗi kết nối database
- Kiểm tra MySQL đã chạy trong XAMPP
- Kiểm tra thông tin trong `config.php`
- Đảm bảo database `quan_ly_diem` đã được tạo

### Không upload được ảnh
- Kiểm tra thư mục `assets/uploads/` có quyền ghi
- Kiểm tra kích thước file (<5MB)
- Kiểm tra định dạng file (jpg, png, gif, webp)

### Lỗi session
- Xóa cookie trình duyệt
- Khởi động lại Apache

## 📞 HỖ TRỢ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra lại hướng dẫn cài đặt
2. Kiểm tra log lỗi Apache/PHP
3. Đảm bảo đã import đúng file database.sql

## 📝 CHANGELOG

### Version 1.0.0
- ✅ Hoàn thành đầy đủ tất cả chức năng theo yêu cầu
- ✅ Giao diện đẹp, responsive
- ✅ Bảo mật tốt
- ✅ Database chuẩn

---

**Phát triển bởi**: NNT & VVL
**Ngày**: 20/03/2026
**Phiên bản**: 1.0.0
