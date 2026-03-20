<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">👨‍🎓</div>
        <div class="stat-info">
            <div class="stat-value"><?= $total_students ?></div>
            <div class="stat-label">Sinh viên</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">👨‍🏫</div>
        <div class="stat-info">
            <div class="stat-value"><?= $total_teachers ?></div>
            <div class="stat-label">Giảng viên</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">📚</div>
        <div class="stat-info">
            <div class="stat-value"><?= $total_subjects ?></div>
            <div class="stat-label">Môn học</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">🏫</div>
        <div class="stat-info">
            <div class="stat-value"><?= $total_classes ?></div>
            <div class="stat-label">Lớp học phần</div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="card-header">
            <h3>📋 Sinh viên theo khoa</h3>
        </div>
        <div class="card-body">
            <?php
            $faculties_sql = "SELECT faculty, COUNT(*) as count 
                             FROM users 
                             WHERE role = 'student' AND faculty IS NOT NULL 
                             GROUP BY faculty 
                             ORDER BY count DESC";
            $faculties_result = $conn->query($faculties_sql);
            
            if ($faculties_result->num_rows > 0):
            ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Khoa</th>
                            <th>Số lượng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $faculties_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= e($row['faculty']) ?></td>
                                <td><span class="badge badge-primary"><?= $row['count'] ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-data">Chưa có dữ liệu</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3>👥 Sinh viên theo lớp</h3>
        </div>
        <div class="card-body">
            <?php
            $classes_sql = "SELECT class_name, COUNT(*) as count 
                           FROM users 
                           WHERE role = 'student' AND class_name IS NOT NULL 
                           GROUP BY class_name 
                           ORDER BY class_name";
            $classes_result = $conn->query($classes_sql);
            
            if ($classes_result->num_rows > 0):
            ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Lớp</th>
                            <th>Số lượng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $classes_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= e($row['class_name']) ?></td>
                                <td><span class="badge badge-success"><?= $row['count'] ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-data">Chưa có dữ liệu</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h3>📅 Hoạt động gần đây</h3>
    </div>
    <div class="card-body">
        <?php
        $activities_sql = "SELECT u.username, u.full_name, u.role, u.created_at 
                          FROM users u 
                          ORDER BY u.created_at DESC 
                          LIMIT 10";
        $activities_result = $conn->query($activities_sql);
        
        if ($activities_result->num_rows > 0):
        ?>
            <div class="activity-list">
                <?php while ($row = $activities_result->fetch_assoc()): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <?php
                            if ($row['role'] === 'student') echo '👨‍🎓';
                            elseif ($row['role'] === 'teacher') echo '👨‍🏫';
                            else echo '👨‍💼';
                            ?>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">
                                Tài khoản mới: <strong><?= e($row['full_name']) ?></strong> (<?= e($row['username']) ?>)
                            </div>
                            <div class="activity-time"><?= formatDate($row['created_at'], 'd/m/Y H:i') ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="no-data">Chưa có hoạt động</p>
        <?php endif; ?>
    </div>
</div>
