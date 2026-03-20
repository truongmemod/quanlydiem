<?php
// Xử lý thêm/sửa/xóa sinh viên
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Tạo MSSV tự động
        $username = generateUserCode('student', $conn);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $dob = $_POST['dob'];
        $faculty = $_POST['faculty'];
        $class_name = $_POST['class_name'];
        $hometown = trim($_POST['hometown']);
        $password = password_hash('123456', PASSWORD_DEFAULT); // Mật khẩu mặc định
        
        // Upload ảnh
        $avatar = 'default.jpg';
        $cover = 'default-cover.jpg';
        
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadImage($_FILES['avatar'], 'avatar');
            if ($uploaded) $avatar = $uploaded;
        }
        
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadImage($_FILES['cover'], 'cover');
            if ($uploaded) $cover = $uploaded;
        }
        
        $sql = "INSERT INTO users (username, password, full_name, role, email, dob, faculty, class_name, hometown, avatar, cover) 
                VALUES (?, ?, ?, 'student', ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $username, $password, $full_name, $email, $dob, $faculty, $class_name, $hometown, $avatar, $cover);
        
        if ($stmt->execute()) {
            setFlash('success', "Thêm sinh viên thành công! MSSV: $username");
        } else {
            setFlash('error', 'Có lỗi xảy ra!');
        }
        redirect('admin.php?page=students');
    }
    
    elseif ($action === 'edit') {
        $id = intval($_POST['id']);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $dob = $_POST['dob'];
        $faculty = $_POST['faculty'];
        $class_name = $_POST['class_name'];
        $hometown = trim($_POST['hometown']);
        
        // Lấy thông tin hiện tại
        $user = getUserById($id, $conn);
        $avatar = $user['avatar'];
        $cover = $user['cover'];
        
        // Upload ảnh mới nếu có
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadImage($_FILES['avatar'], 'avatar');
            if ($uploaded) {
                deleteImage($avatar, 'avatar');
                $avatar = $uploaded;
            }
        }
        
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadImage($_FILES['cover'], 'cover');
            if ($uploaded) {
                deleteImage($cover, 'cover');
                $cover = $uploaded;
            }
        }
        
        $sql = "UPDATE users SET full_name=?, email=?, dob=?, faculty=?, class_name=?, hometown=?, avatar=?, cover=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssi", $full_name, $email, $dob, $faculty, $class_name, $hometown, $avatar, $cover, $id);
        
        if ($stmt->execute()) {
            setFlash('success', 'Cập nhật thông tin thành công!');
        } else {
            setFlash('error', 'Có lỗi xảy ra!');
        }
        redirect('admin.php?page=students');
    }
    
    elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        
        // Lấy thông tin để xóa ảnh
        $user = getUserById($id, $conn);
        if ($user) {
            deleteImage($user['avatar'], 'avatar');
            deleteImage($user['cover'], 'cover');
            
            $sql = "DELETE FROM users WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                setFlash('success', 'Xóa sinh viên thành công!');
            } else {
                setFlash('error', 'Có lỗi xảy ra!');
            }
        }
        redirect('admin.php?page=students');
    }
    
    elseif ($action === 'toggle_status') {
        $id = intval($_POST['id']);
        $current_status = $_POST['current_status'];
        $new_status = ($current_status === 'active') ? 'locked' : 'active';
        
        $sql = "UPDATE users SET status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_status, $id);
        
        if ($stmt->execute()) {
            $msg = ($new_status === 'locked') ? 'Khóa tài khoản thành công!' : 'Mở khóa tài khoản thành công!';
            setFlash('success', $msg);
        } else {
            setFlash('error', 'Có lỗi xảy ra!');
        }
        redirect('admin.php?page=students');
    }
}

// Lấy danh sách sinh viên
$search = $_GET['search'] ?? '';
$faculty_filter = $_GET['faculty'] ?? '';
$class_filter = $_GET['class'] ?? '';

$where = "role='student'";
if ($search) {
    $where .= " AND (username LIKE '%$search%' OR full_name LIKE '%$search%' OR email LIKE '%$search%')";
}
if ($faculty_filter) {
    $where .= " AND faculty='$faculty_filter'";
}
if ($class_filter) {
    $where .= " AND class_name='$class_filter'";
}

$sql = "SELECT * FROM users WHERE $where ORDER BY id DESC";
$students = $conn->query($sql);
?>

<div class="page-header">
    <button class="btn btn-primary" onclick="openModal('addStudentModal')">
        <span>➕</span> Thêm sinh viên
    </button>
    
    <div class="filter-group">
        <form method="GET" class="filter-form">
            <input type="hidden" name="page" value="students">
            <input type="text" name="search" placeholder="🔍 Tìm theo MSSV, tên, email..." value="<?= e($search) ?>">
            
            <select name="faculty">
                <option value="">-- Tất cả khoa --</option>
                <?php foreach (getFaculties($conn) as $f): ?>
                    <option value="<?= e($f) ?>" <?= $f === $faculty_filter ? 'selected' : '' ?>><?= e($f) ?></option>
                <?php endforeach; ?>
            </select>
            
            <select name="class">
                <option value="">-- Tất cả lớp --</option>
                <?php foreach (getClasses($conn) as $c): ?>
                    <option value="<?= e($c) ?>" <?= $c === $class_filter ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn btn-secondary">Lọc</button>
            <a href="?page=students" class="btn btn-secondary">Xóa lọc</a>
        </form>
    </div>
</div>

<div class="data-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>MSSV</th>
                <th>Họ tên</th>
                <th>Khoa</th>
                <th>Lớp</th>
                <th>Email</th>
                <th>Ngày sinh</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($students->num_rows > 0): ?>
                <?php while ($student = $students->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= e($student['username']) ?></strong></td>
                        <td>
                            <div class="user-cell">
                                <img src="<?= getAvatarPath($student['avatar']) ?>" alt="Avatar" class="table-avatar">
                                <span><?= e($student['full_name']) ?></span>
                            </div>
                        </td>
                        <td><?= e($student['faculty']) ?></td>
                        <td><span class="badge badge-info"><?= e($student['class_name']) ?></span></td>
                        <td><?= e($student['email']) ?></td>
                        <td><?= formatDate($student['dob']) ?></td>
                        <td>
                            <?php if ($student['status'] === 'active'): ?>
                                <span class="badge badge-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Khóa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon btn-info" onclick='viewStudent(<?= json_encode($student) ?>)' title="Xem chi tiết">
                                    👁️
                                </button>
                                <button class="btn-icon btn-primary" onclick='editStudent(<?= json_encode($student) ?>)' title="Sửa">
                                    ✏️
                                </button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận <?= $student['status'] === 'active' ? 'khóa' : 'mở khóa' ?> tài khoản?')">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="id" value="<?= $student['id'] ?>">
                                    <input type="hidden" name="current_status" value="<?= $student['status'] ?>">
                                    <button type="submit" class="btn-icon btn-warning" title="<?= $student['status'] === 'active' ? 'Khóa' : 'Mở khóa' ?>">
                                        <?= $student['status'] === 'active' ? '🔒' : '🔓' ?>
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận xóa sinh viên này?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $student['id'] ?>">
                                    <button type="submit" class="btn-icon btn-danger" title="Xóa">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">Không có dữ liệu</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal thêm sinh viên -->
<div id="addStudentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>➕ Thêm sinh viên mới</h3>
            <button class="close-modal" onclick="closeModal('addStudentModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Họ tên <span class="required">*</span></label>
                        <input type="text" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Ngày sinh <span class="required">*</span></label>
                        <input type="date" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label>Quê quán</label>
                        <input type="text" name="hometown">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Khoa <span class="required">*</span></label>
                        <select name="faculty" required>
                            <option value="">-- Chọn khoa --</option>
                            <option value="Khoa Công nghệ thông tin">Khoa Công nghệ thông tin</option>
                            <option value="Khoa Toán - Tin">Khoa Toán - Tin</option>
                            <option value="Khoa Ngoại ngữ">Khoa Ngoại ngữ</option>
                            <option value="Khoa Kinh tế">Khoa Kinh tế</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Lớp <span class="required">*</span></label>
                        <input type="text" name="class_name" required placeholder="VD: CT01">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Ảnh đại diện</label>
                        <input type="file" name="avatar" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Ảnh nền</label>
                        <input type="file" name="cover" accept="image/*">
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <span class="alert-icon">ℹ️</span>
                    <span>MSSV sẽ được tạo tự động. Mật khẩu mặc định: <strong>123456</strong></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addStudentModal')">Hủy</button>
                <button type="submit" class="btn btn-primary">Thêm sinh viên</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal sửa sinh viên -->
<div id="editStudentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>✏️ Sửa thông tin sinh viên</h3>
            <button class="close-modal" onclick="closeModal('editStudentModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data" id="editStudentForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>MSSV</label>
                    <input type="text" id="edit_username" disabled>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Họ tên <span class="required">*</span></label>
                        <input type="text" name="full_name" id="edit_full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" id="edit_email" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Ngày sinh <span class="required">*</span></label>
                        <input type="date" name="dob" id="edit_dob" required>
                    </div>
                    <div class="form-group">
                        <label>Quê quán</label>
                        <input type="text" name="hometown" id="edit_hometown">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Khoa <span class="required">*</span></label>
                        <select name="faculty" id="edit_faculty" required>
                            <option value="">-- Chọn khoa --</option>
                            <option value="Khoa Công nghệ thông tin">Khoa Công nghệ thông tin</option>
                            <option value="Khoa Toán - Tin">Khoa Toán - Tin</option>
                            <option value="Khoa Ngoại ngữ">Khoa Ngoại ngữ</option>
                            <option value="Khoa Kinh tế">Khoa Kinh tế</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Lớp <span class="required">*</span></label>
                        <input type="text" name="class_name" id="edit_class_name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Ảnh đại diện mới</label>
                        <input type="file" name="avatar" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Ảnh nền mới</label>
                        <input type="file" name="cover" accept="image/*">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editStudentModal')">Hủy</button>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal xem chi tiết -->
<div id="viewStudentModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3>👨‍🎓 Thông tin chi tiết sinh viên</h3>
            <button class="close-modal" onclick="closeModal('viewStudentModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="student-profile">
                <div class="profile-cover" id="view_cover_img"></div>
                <div class="profile-info">
                    <img id="view_avatar_img" class="profile-avatar" alt="Avatar">
                    <div class="profile-details">
                        <h2 id="view_full_name"></h2>
                        <p class="profile-username" id="view_username"></p>
                    </div>
                </div>
                <div class="profile-data">
                    <div class="data-row">
                        <span class="data-label">📧 Email:</span>
                        <span class="data-value" id="view_email"></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">🎂 Ngày sinh:</span>
                        <span class="data-value" id="view_dob"></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">🏫 Khoa:</span>
                        <span class="data-value" id="view_faculty"></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">👥 Lớp:</span>
                        <span class="data-value" id="view_class"></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">🏠 Quê quán:</span>
                        <span class="data-value" id="view_hometown"></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">📅 Ngày tạo:</span>
                        <span class="data-value" id="view_created"></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">🔐 Trạng thái:</span>
                        <span class="data-value" id="view_status"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editStudent(student) {
    document.getElementById('edit_id').value = student.id;
    document.getElementById('edit_username').value = student.username;
    document.getElementById('edit_full_name').value = student.full_name;
    document.getElementById('edit_email').value = student.email || '';
    document.getElementById('edit_dob').value = student.dob || '';
    document.getElementById('edit_hometown').value = student.hometown || '';
    document.getElementById('edit_faculty').value = student.faculty || '';
    document.getElementById('edit_class_name').value = student.class_name || '';
    openModal('editStudentModal');
}

function viewStudent(student) {
    document.getElementById('view_full_name').textContent = student.full_name;
    document.getElementById('view_username').textContent = student.username;
    document.getElementById('view_email').textContent = student.email || '-';
    document.getElementById('view_dob').textContent = student.dob ? formatDate(student.dob) : '-';
    document.getElementById('view_faculty').textContent = student.faculty || '-';
    document.getElementById('view_class').textContent = student.class_name || '-';
    document.getElementById('view_hometown').textContent = student.hometown || '-';
    document.getElementById('view_created').textContent = student.created_at ? formatDate(student.created_at) : '-';
    document.getElementById('view_status').innerHTML = student.status === 'active' 
        ? '<span class="badge badge-success">Hoạt động</span>' 
        : '<span class="badge badge-danger">Khóa</span>';
    
    const avatarPath = student.avatar && student.avatar !== 'default.jpg' 
        ? 'assets/uploads/avatar/' + student.avatar 
        : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(student.full_name) + '&background=4F46E5&color=fff&size=200';
    document.getElementById('view_avatar_img').src = avatarPath;
    
    const coverPath = student.cover && student.cover !== 'default-cover.jpg' 
        ? 'assets/uploads/cover/' + student.cover 
        : 'https://images.unsplash.com/photo-1557683316-973673baf926?w=1200&h=400&fit=crop';
    document.getElementById('view_cover_img').style.backgroundImage = 'url(' + coverPath + ')';
    
    openModal('viewStudentModal');
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('vi-VN');
}
</script>
