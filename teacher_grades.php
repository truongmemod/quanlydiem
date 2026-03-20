<?php
$class_id = intval($_GET['class_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_score') {
        $student_id = intval($_POST['student_id']);
        $subject_id = $_POST['subject_id'];
        $score_cc = floatval($_POST['score_cc']);
        $score_mid = floatval($_POST['score_mid']);
        $score_final = floatval($_POST['score_final']);
        $semester = $_POST['semester'];
        
        // Kiểm tra xem đã có điểm chưa
        $check_sql = "SELECT id, is_locked FROM grades WHERE student_id=? AND subject_id=?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("is", $student_id, $subject_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if ($row['is_locked']) {
                setFlash('error', 'Bảng điểm đã bị khóa, không thể sửa!');
            } else {
                $sql = "UPDATE grades SET score_cc=?, score_mid=?, score_final=?, semester=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("dddsi", $score_cc, $score_mid, $score_final, $semester, $row['id']);
                $stmt->execute();
                setFlash('success', 'Cập nhật điểm thành công!');
            }
        } else {
            $sql = "INSERT INTO grades (student_id, subject_id, class_id, score_cc, score_mid, score_final, semester) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isiddds", $student_id, $subject_id, $class_id, $score_cc, $score_mid, $score_final, $semester);
            $stmt->execute();
            setFlash('success', 'Thêm điểm thành công!');
        }
        redirect("teacher.php?page=grades&class_id=$class_id");
    }
    
    elseif ($action === 'lock_grades') {
        $subject_id = $_POST['subject_id'];
        $sql = "UPDATE grades SET is_locked=1 WHERE subject_id=? AND class_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $subject_id, $class_id);
        if ($stmt->execute()) {
            setFlash('success', 'Đã khóa bảng điểm!');
        }
        redirect("teacher.php?page=grades&class_id=$class_id");
    }
}

if ($class_id) {
    $class_info = getClassById($class_id, $conn);
    if (!$class_info || $class_info['teacher_id'] != $teacher_id) {
        die("Bạn không có quyền truy cập lớp này!");
    }
    
    $subject = getSubjectById($class_info['subject_id'], $conn);
    
    // Lấy danh sách sinh viên trong lớp
    $students_sql = "SELECT u.*, g.score_cc, g.score_mid, g.score_final, g.is_locked
                     FROM users u
                     LEFT JOIN grades g ON u.id = g.student_id AND g.subject_id = ?
                     WHERE u.role='student' AND u.class_name = ?
                     ORDER BY u.username";
    $stmt = $conn->prepare($students_sql);
    $stmt->bind_param("ss", $class_info['subject_id'], $class_info['class_name']);
    $stmt->execute();
    $students = $stmt->get_result();
    
    $is_locked = false;
    if ($students->num_rows > 0) {
        $students->data_seek(0);
        $first = $students->fetch_assoc();
        $is_locked = $first['is_locked'] == 1;
        $students->data_seek(0);
    }
?>
<div class="data-card">
    <div class="card-header">
        <h3>✍️ Nhập điểm: <?= e($class_info['subject_name']) ?> - Lớp <?= e($class_info['class_name']) ?></h3>
        <div>
            <span class="badge badge-info"><?= e($class_info['semester']) ?></span>
            <span class="badge badge-primary"><?= $subject['credits'] ?> TC</span>
            <?php if ($is_locked): ?>
                <span class="badge badge-danger">🔒 Đã khóa</span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="grade-info">
        <strong>Cấu trúc điểm:</strong> 
        Chuyên cần <?= $subject['percent_cc'] ?>% + 
        Giữa kỳ <?= $subject['percent_mid'] ?>% + 
        Cuối kỳ <?= $subject['percent_final'] ?>%
    </div>
    
    <?php if (!$is_locked): ?>
    <div class="alert alert-warning">
        <span class="alert-icon">⚠️</span>
        <span>Sau khi khóa bảng điểm, bạn sẽ không thể chỉnh sửa!</span>
    </div>
    <?php endif; ?>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>MSSV</th>
                <th>Họ tên</th>
                <th>Chuyên cần</th>
                <th>Giữa kỳ</th>
                <th>Cuối kỳ</th>
                <th>Tổng</th>
                <th>Điểm chữ</th>
                <th>Hệ 4</th>
                <?php if (!$is_locked): ?><th>Thao tác</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($student = $students->fetch_assoc()): 
                $total = calculateTotalScore(
                    $student['score_cc'], 
                    $student['score_mid'], 
                    $student['score_final'],
                    $subject['percent_cc'],
                    $subject['percent_mid'],
                    $subject['percent_final']
                );
            ?>
                <tr>
                    <td><?= e($student['username']) ?></td>
                    <td><?= e($student['full_name']) ?></td>
                    <td><?= $student['score_cc'] ?? '-' ?></td>
                    <td><?= $student['score_mid'] ?? '-' ?></td>
                    <td><?= $student['score_final'] ?? '-' ?></td>
                    <td><strong><?= $total ?? '-' ?></strong></td>
                    <td><span class="badge badge-primary"><?= scoreToChar($total) ?></span></td>
                    <td><?= scoreTo4($total) ?></td>
                    <?php if (!$is_locked): ?>
                    <td>
                        <button class="btn-icon btn-primary" 
                                onclick='editGrade(<?= json_encode($student) ?>, "<?= $class_info['subject_id'] ?>", "<?= $class_info['semester'] ?>")'>
                            ✏️
                        </button>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <?php if (!$is_locked): ?>
    <form method="POST" style="margin-top:20px;" onsubmit="return confirm('Xác nhận khóa bảng điểm? Sau khi khóa sẽ không thể sửa!')">
        <input type="hidden" name="action" value="lock_grades">
        <input type="hidden" name="subject_id" value="<?= $class_info['subject_id'] ?>">
        <button type="submit" class="btn btn-danger">🔒 Khóa bảng điểm</button>
    </form>
    <?php endif; ?>
</div>

<div id="editGradeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>✍️ Nhập/sửa điểm</h3>
            <button class="close-modal" onclick="closeModal('editGradeModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="save_score">
            <input type="hidden" name="student_id" id="grade_student_id">
            <input type="hidden" name="subject_id" id="grade_subject_id">
            <input type="hidden" name="semester" id="grade_semester">
            <div class="modal-body">
                <div class="form-group">
                    <label>Sinh viên</label>
                    <input type="text" id="grade_student_name" disabled>
                </div>
                <div class="form-group">
                    <label>Điểm chuyên cần <span class="required">*</span></label>
                    <input type="number" name="score_cc" id="grade_cc" step="0.1" min="0" max="10" required>
                </div>
                <div class="form-group">
                    <label>Điểm giữa kỳ <span class="required">*</span></label>
                    <input type="number" name="score_mid" id="grade_mid" step="0.1" min="0" max="10" required>
                </div>
                <div class="form-group">
                    <label>Điểm cuối kỳ <span class="required">*</span></label>
                    <input type="number" name="score_final" id="grade_final" step="0.1" min="0" max="10" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editGradeModal')">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu điểm</button>
            </div>
        </form>
    </div>
</div>

<script>
function editGrade(student, subject_id, semester) {
    document.getElementById('grade_student_id').value = student.id;
    document.getElementById('grade_subject_id').value = subject_id;
    document.getElementById('grade_semester').value = semester;
    document.getElementById('grade_student_name').value = student.username + ' - ' + student.full_name;
    document.getElementById('grade_cc').value = student.score_cc || '';
    document.getElementById('grade_mid').value = student.score_mid || '';
    document.getElementById('grade_final').value = student.score_final || '';
    openModal('editGradeModal');
}
</script>
<?php } else { ?>
    <div class="alert alert-info">
        <span class="alert-icon">ℹ️</span>
        <span>Vui lòng chọn lớp để nhập điểm</span>
    </div>
<?php } ?>
