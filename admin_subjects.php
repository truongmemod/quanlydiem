<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $subject_id = strtoupper(trim($_POST['subject_id']));
        $name = trim($_POST['name']);
        $credits = intval($_POST['credits']);
        $percent_cc = intval($_POST['percent_cc']);
        $percent_mid = intval($_POST['percent_mid']);
        $percent_final = intval($_POST['percent_final']);
        
        $sql = "INSERT INTO subjects (subject_id, name, credits, percent_cc, percent_mid, percent_final) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiiii", $subject_id, $name, $credits, $percent_cc, $percent_mid, $percent_final);
        
        if ($stmt->execute()) {
            setFlash('success', 'Thêm môn học thành công!');
        } else {
            setFlash('error', 'Mã môn học đã tồn tại!');
        }
        redirect('admin.php?page=subjects');
    }
    
    elseif ($action === 'edit') {
        $subject_id = $_POST['subject_id'];
        $name = trim($_POST['name']);
        $credits = intval($_POST['credits']);
        $percent_cc = intval($_POST['percent_cc']);
        $percent_mid = intval($_POST['percent_mid']);
        $percent_final = intval($_POST['percent_final']);
        
        $sql = "UPDATE subjects SET name=?, credits=?, percent_cc=?, percent_mid=?, percent_final=? WHERE subject_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siiiis", $name, $credits, $percent_cc, $percent_mid, $percent_final, $subject_id);
        
        if ($stmt->execute()) {
            setFlash('success', 'Cập nhật môn học thành công!');
        } else {
            setFlash('error', 'Có lỗi xảy ra!');
        }
        redirect('admin.php?page=subjects');
    }
    
    elseif ($action === 'delete') {
        $subject_id = $_POST['subject_id'];
        
        $sql = "DELETE FROM subjects WHERE subject_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $subject_id);
        
        if ($stmt->execute()) {
            setFlash('success', 'Xóa môn học thành công!');
        } else {
            setFlash('error', 'Không thể xóa môn học đang có lớp học phần!');
        }
        redirect('admin.php?page=subjects');
    }
}

$search = $_GET['search'] ?? '';
$where = $search ? "WHERE subject_id LIKE '%$search%' OR name LIKE '%$search%'" : '';
$sql = "SELECT * FROM subjects $where ORDER BY subject_id";
$subjects = $conn->query($sql);
?>

<div class="page-header">
    <button class="btn btn-primary" onclick="openModal('addSubjectModal')">
        <span>➕</span> Thêm môn học
    </button>
    <form method="GET" class="filter-form">
        <input type="hidden" name="page" value="subjects">
        <input type="text" name="search" placeholder="🔍 Tìm theo mã hoặc tên môn..." value="<?= e($search) ?>">
        <button type="submit" class="btn btn-secondary">Tìm</button>
        <?php if ($search): ?>
            <a href="?page=subjects" class="btn btn-secondary">Xóa lọc</a>
        <?php endif; ?>
    </form>
</div>

<div class="data-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Mã môn</th>
                <th>Tên môn học</th>
                <th>Số TC</th>
                <th>% Chuyên cần</th>
                <th>% Giữa kỳ</th>
                <th>% Cuối kỳ</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($subjects->num_rows > 0): ?>
                <?php while ($subject = $subjects->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= e($subject['subject_id']) ?></strong></td>
                        <td><?= e($subject['name']) ?></td>
                        <td><span class="badge badge-primary"><?= $subject['credits'] ?></span></td>
                        <td><?= $subject['percent_cc'] ?>%</td>
                        <td><?= $subject['percent_mid'] ?>%</td>
                        <td><?= $subject['percent_final'] ?>%</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon btn-primary" onclick='editSubject(<?= json_encode($subject) ?>)'>✏️</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận xóa môn học?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="subject_id" value="<?= $subject['subject_id'] ?>">
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

<div id="addSubjectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>➕ Thêm môn học</h3>
            <button class="close-modal" onclick="closeModal('addSubjectModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Mã môn học <span class="required">*</span></label>
                        <input type="text" name="subject_id" required placeholder="VD: MATH101">
                    </div>
                    <div class="form-group">
                        <label>Tên môn học <span class="required">*</span></label>
                        <input type="text" name="name" required placeholder="VD: Toán cao cấp 1">
                    </div>
                </div>
                <div class="form-group">
                    <label>Số tín chỉ <span class="required">*</span></label>
                    <input type="number" name="credits" required min="1" max="10" value="3">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>% Chuyên cần <span class="required">*</span></label>
                        <input type="number" name="percent_cc" required min="0" max="100" value="10">
                    </div>
                    <div class="form-group">
                        <label>% Giữa kỳ <span class="required">*</span></label>
                        <input type="number" name="percent_mid" required min="0" max="100" value="30">
                    </div>
                    <div class="form-group">
                        <label>% Cuối kỳ <span class="required">*</span></label>
                        <input type="number" name="percent_final" required min="0" max="100" value="60">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addSubjectModal')">Hủy</button>
                <button type="submit" class="btn btn-primary">Thêm môn học</button>
            </div>
        </form>
    </div>
</div>

<div id="editSubjectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>✏️ Sửa môn học</h3>
            <button class="close-modal" onclick="closeModal('editSubjectModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="subject_id" id="edit_subject_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Mã môn học</label>
                    <input type="text" id="edit_subject_id_display" disabled>
                </div>
                <div class="form-group">
                    <label>Tên môn học <span class="required">*</span></label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Số tín chỉ <span class="required">*</span></label>
                    <input type="number" name="credits" id="edit_credits" required min="1" max="10">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>% Chuyên cần <span class="required">*</span></label>
                        <input type="number" name="percent_cc" id="edit_percent_cc" required min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label>% Giữa kỳ <span class="required">*</span></label>
                        <input type="number" name="percent_mid" id="edit_percent_mid" required min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label>% Cuối kỳ <span class="required">*</span></label>
                        <input type="number" name="percent_final" id="edit_percent_final" required min="0" max="100">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editSubjectModal')">Hủy</button>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSubject(subject) {
    document.getElementById('edit_subject_id').value = subject.subject_id;
    document.getElementById('edit_subject_id_display').value = subject.subject_id;
    document.getElementById('edit_name').value = subject.name;
    document.getElementById('edit_credits').value = subject.credits;
    document.getElementById('edit_percent_cc').value = subject.percent_cc;
    document.getElementById('edit_percent_mid').value = subject.percent_mid;
    document.getElementById('edit_percent_final').value = subject.percent_final;
    openModal('editSubjectModal');
}
</script>
