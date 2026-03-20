<?php
// Xử lý thêm/sửa/xóa giảng viên (tương tự students)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $username = generateUserCode('teacher', $conn);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $dob = $_POST['dob'];
        $faculty = $_POST['faculty'];
        $password = password_hash('123456', PASSWORD_DEFAULT);
        
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
        
        $sql = "INSERT INTO users (username, password, full_name, role, email, dob, faculty, avatar, cover) 
                VALUES (?, ?, ?, 'teacher', ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $username, $password, $full_name, $email, $dob, $faculty, $avatar, $cover);
        
        if ($stmt->execute()) {
            setFlash('success', "Thêm giảng viên thành công! MSGV: $username");
        } else {
            setFlash('error', 'Có lỗi xảy ra!');
        }
        redirect('admin.php?page=teachers');
    }
    
    elseif ($action === 'edit') {
        $id = intval($_POST['id']);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $dob = $_POST['dob'];
        $faculty = $_POST['faculty'];
        
        $user = getUserById($id, $conn);
        $avatar = $user['avatar'];
        $cover = $user['cover'];
        
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
        
        $sql = "UPDATE users SET full_name=?, email=?, dob=?, faculty=?, avatar=?, cover=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $full_name, $email, $dob, $faculty, $avatar, $cover, $id);
        
        if ($stmt->execute()) {
            setFlash('success', 'Cập nhật thông tin thành công!');
        } else {
            setFlash('error', 'Có lỗi xảy ra!');
        }
        redirect('admin.php?page=teachers');
    }
    
    elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        $user = getUserById($id, $conn);
        if ($user) {
            deleteImage($user['avatar'], 'avatar');
            deleteImage($user['cover'], 'cover');
            
            $sql = "DELETE FROM users WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                setFlash('success', 'Xóa giảng viên thành công!');
            } else {
                setFlash('error', 'Có lỗi xảy ra!');
            }
        }
        redirect('admin.php?page=teachers');
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
        redirect('admin.php?page=teachers');
    }
}

// Lấy danh sách giảng viên
$search = $_GET['search'] ?? '';
$faculty_filter = $_GET['faculty'] ?? '';

$where = "role='teacher'";
if ($search) {
    $where .= " AND (username LIKE '%$search%' OR full_name LIKE '%$search%' OR email LIKE '%$search%')";
}
if ($faculty_filter) {
    $where .= " AND faculty='$faculty_filter'";
}

$sql = "SELECT * FROM users WHERE $where ORDER BY id DESC";
$teachers = $conn->query($sql);
?>

<div class="page-header">
    <button class="btn btn-primary" onclick="openModal('addTeacherModal')">
        <span>➕</span> Thêm giảng viên
    </button>
    
    <div class="filter-group">
        <form method="GET" class="filter-form">
            <input type="hidden" name="page" value="teachers">
            <input type="text" name="search" placeholder="🔍 Tìm theo MSGV, tên, email..." value="<?= e($search) ?>">
            
            <select name="faculty">
                <option value="">-- Tất cả khoa --</option>
                <?php foreach (getFaculties($conn) as $f): ?>
                    <option value="<?= e($f) ?>" <?= $f === $faculty_filter ? 'selected' : '' ?>><?= e($f) ?></option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn btn-secondary">Lọc</button>
            <a href="?page=teachers" class="btn btn-secondary">Xóa lọc</a>
        </form>
    </div>
</div>

<div class="data-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>MSGV</th>
                <th>Họ tên</th>
                <th>Khoa</th>
                <th>Email</th>
                <th>Ngày sinh</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($teachers->num_rows > 0): ?>
                <?php while ($teacher = $teachers->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= e($teacher['username']) ?></strong></td>
                        <td>
                            <div class="user-cell">
                                <img src="<?= getAvatarPath($teacher['avatar']) ?>" alt="Avatar" class="table-avatar">
                                <span><?= e($teacher['full_name']) ?></span>
                            </div>
                        </td>
                        <td><?= e($teacher['faculty']) ?></td>
                        <td><?= e($teacher['email']) ?></td>
                        <td><?= formatDate($teacher['dob']) ?></td>
                        <td>
                            <?php if ($teacher['status'] === 'active'): ?>
                                <span class="badge badge-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Khóa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon btn-info" onclick='viewTeacher(<?= json_encode($teacher) ?>)' title="Xem chi tiết">👁️</button>
                                <button class="btn-icon btn-primary" onclick='editTeacher(<?= json_encode($teacher) ?>)' title="Sửa">✏️</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận <?= $teacher['status'] === 'active' ? 'khóa' : 'mở khóa' ?> tài khoản?')">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="id" value="<?= $teacher['id'] ?>">
                                    <input type="hidden" name="current_status" value="<?= $teacher['status'] ?>">
                                    <button type="submit" class="btn-icon btn-warning"><?= $teacher['status'] === 'active' ? '🔒' : '🔓' ?></button>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận xóa giảng viên?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $teacher['id'] ?>">
                                    <button type="submit" class="btn-icon btn-danger">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">Không có dữ liệu</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal tương tự students, chỉ bỏ class_name và hometown -->
<div id="addTeacherModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>➕ Thêm giảng viên mới</h3>
            <button class="close-modal" onclick="closeModal('addTeacherModal')">&times;</button>
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
                        <label>Khoa <span class="required">*</span></label>
                        <select name="faculty" required>
                            <option value="">-- Chọn khoa --</option>
                            <option value="Khoa Công nghệ thông tin">Khoa Công nghệ thông tin</option>
                            <option value="Khoa Toán - Tin">Khoa Toán - Tin</option>
                            <option value="Khoa Ngoại ngữ">Khoa Ngoại ngữ</option>
                            <option value="Khoa Kinh tế">Khoa Kinh tế</option>
                        </select>
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
                    <span>MSGV sẽ được tạo tự động. Mật khẩu mặc định: <strong>123456</strong></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addTeacherModal')">Hủy</button>
                <button type="submit" class="btn btn-primary">Thêm giảng viên</button>
            </div>
        </form>
    </div>
</div>

<div id="editTeacherModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>✏️ Sửa thông tin giảng viên</h3>
            <button class="close-modal" onclick="closeModal('editTeacherModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>MSGV</label>
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
                        <label>Khoa <span class="required">*</span></label>
                        <select name="faculty" id="edit_faculty" required>
                            <option value="">-- Chọn khoa --</option>
                            <option value="Khoa Công nghệ thông tin">Khoa Công nghệ thông tin</option>
                            <option value="Khoa Toán - Tin">Khoa Toán - Tin</option>
                            <option value="Khoa Ngoại ngữ">Khoa Ngoại ngữ</option>
                            <option value="Khoa Kinh tế">Khoa Kinh tế</option>
                        </select>
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
                <button type="button" class="btn btn-secondary" onclick="closeModal('editTeacherModal')">Hủy</button>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<div id="viewTeacherModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3>👨‍🏫 Thông tin chi tiết giảng viên</h3>
            <button class="close-modal" onclick="closeModal('viewTeacherModal')">&times;</button>
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
function editTeacher(teacher) {
    document.getElementById('edit_id').value = teacher.id;
    document.getElementById('edit_username').value = teacher.username;
    document.getElementById('edit_full_name').value = teacher.full_name;
    document.getElementById('edit_email').value = teacher.email || '';
    document.getElementById('edit_dob').value = teacher.dob || '';
    document.getElementById('edit_faculty').value = teacher.faculty || '';
    openModal('editTeacherModal');
}

function viewTeacher(teacher) {
    document.getElementById('view_full_name').textContent = teacher.full_name;
    document.getElementById('view_username').textContent = teacher.username;
    document.getElementById('view_email').textContent = teacher.email || '-';
    document.getElementById('view_dob').textContent = teacher.dob ? formatDate(teacher.dob) : '-';
    document.getElementById('view_faculty').textContent = teacher.faculty || '-';
    document.getElementById('view_created').textContent = teacher.created_at ? formatDate(teacher.created_at) : '-';
    document.getElementById('view_status').innerHTML = teacher.status === 'active' 
        ? '<span class="badge badge-success">Hoạt động</span>' 
        : '<span class="badge badge-danger">Khóa</span>';
    
    const avatarPath = teacher.avatar && teacher.avatar !== 'default.jpg' 
        ? 'assets/uploads/avatar/' + teacher.avatar 
        : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(teacher.full_name) + '&background=4F46E5&color=fff&size=200';
    document.getElementById('view_avatar_img').src = avatarPath;
    
    const coverPath = teacher.cover && teacher.cover !== 'default-cover.jpg' 
        ? 'assets/uploads/cover/' + teacher.cover 
        : 'https://images.unsplash.com/photo-1557683316-973673baf926?w=1200&h=400&fit=crop';
    document.getElementById('view_cover_img').style.backgroundImage = 'url(' + coverPath + ')';
    
    openModal('viewTeacherModal');
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('vi-VN');
}
</script>
