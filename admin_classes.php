<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $subject_id = $_POST['subject_id'];
        $teacher_id = intval($_POST['teacher_id']);
        $semester = trim($_POST['semester']);
        $class_name = trim($_POST['class_name']);
        
        $sql = "INSERT INTO classes (subject_id, teacher_id, semester, class_name) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siss", $subject_id, $teacher_id, $semester, $class_name);
        
        if ($stmt->execute()) {
            setFlash('success', 'Phân công giảng dạy thành công!');
        } else {
            setFlash('error', 'Có lỗi xảy ra!');
        }
        redirect('admin.php?page=classes');
    }
    
    elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        
        $sql = "DELETE FROM classes WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            setFlash('success', 'Xóa lớp học phần thành công!');
        } else {
            setFlash('error', 'Có lỗi xảy ra!');
        }
        redirect('admin.php?page=classes');
    }
}

$sql = "SELECT c.*, s.name as subject_name, u.full_name as teacher_name 
        FROM classes c
        JOIN subjects s ON c.subject_id = s.subject_id
        JOIN users u ON c.teacher_id = u.id
        ORDER BY c.semester DESC, s.name";
$classes = $conn->query($sql);

$subjects_sql = "SELECT * FROM subjects ORDER BY name";
$subjects_list = $conn->query($subjects_sql);

$teachers_sql = "SELECT id, username, full_name FROM users WHERE role='teacher' AND status='active' ORDER BY full_name";
$teachers_list = $conn->query($teachers_sql);
?>

<div class="page-header">
    <button class="btn btn-primary" onclick="openModal('addClassModal')">
        <span>➕</span> Phân công giảng dạy
    </button>
</div>

<div class="data-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Mã môn</th>
                <th>Tên môn</th>
                <th>Giảng viên</th>
                <th>Học kỳ</th>
                <th>Lớp HP</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($classes->num_rows > 0): ?>
                <?php while ($class = $classes->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= e($class['subject_id']) ?></strong></td>
                        <td><?= e($class['subject_name']) ?></td>
                        <td><?= e($class['teacher_name']) ?></td>
                        <td><span class="badge badge-info"><?= e($class['semester']) ?></span></td>
                        <td><span class="badge badge-success"><?= e($class['class_name']) ?></span></td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận xóa?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $class['id'] ?>">
                                <button type="submit" class="btn-icon btn-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">Chưa có phân công giảng dạy</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="addClassModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>➕ Phân công giảng dạy</h3>
            <button class="close-modal" onclick="closeModal('addClassModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group">
                    <label>Môn học <span class="required">*</span></label>
                    <select name="subject_id" required>
                        <option value="">-- Chọn môn học --</option>
                        <?php 
                        $subjects_list->data_seek(0);
                        while ($s = $subjects_list->fetch_assoc()): 
                        ?>
                            <option value="<?= e($s['subject_id']) ?>"><?= e($s['subject_id']) ?> - <?= e($s['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Giảng viên <span class="required">*</span></label>
                    <select name="teacher_id" required>
                        <option value="">-- Chọn giảng viên --</option>
                        <?php 
                        $teachers_list->data_seek(0);
                        while ($t = $teachers_list->fetch_assoc()): 
                        ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['username']) ?> - <?= e($t['full_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Học kỳ <span class="required">*</span></label>
                        <input type="text" name="semester" required placeholder="VD: HK1 2024-2025">
                    </div>
                    <div class="form-group">
                        <label>Lớp học phần <span class="required">*</span></label>
                        <input type="text" name="class_name" required placeholder="VD: CT01">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addClassModal')">Hủy</button>
                <button type="submit" class="btn btn-primary">Phân công</button>
            </div>
        </form>
    </div>
</div>
