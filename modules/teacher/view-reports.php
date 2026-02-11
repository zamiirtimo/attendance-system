<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

if (!isTeacher()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$page = 'reports';
$page_title = 'My Class Reports';

$teacher_id = $_SESSION['user_id'];

// Get filter parameters
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$class_id = $_GET['class_id'] ?? '';
$status = $_GET['status'] ?? '';

// Get teacher's classes
$classes = mysqli_query($conn, 
    "SELECT * FROM classes WHERE teacher_id = $teacher_id ORDER BY class_name"
);

// Build query
$query = "SELECT a.*, s.full_name, s.student_code, c.class_name, c.course_name
          FROM attendance a
          JOIN students s ON a.student_id = s.student_id
          JOIN classes c ON a.class_id = c.class_id
          WHERE c.teacher_id = $teacher_id
          AND a.date BETWEEN '$date_from' AND '$date_to'";

if (!empty($class_id)) {
    $query .= " AND a.class_id = " . intval($class_id);
}

if (!empty($status)) {
    $query .= " AND a.status = '$status'";
}

$query .= " ORDER BY a.date DESC, c.class_name, s.full_name";

$reports = mysqli_query($conn, $query);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="bi bi-file-earmark-bar-graph me-2"></i>My Class Reports
    </h1>
    <button onclick="window.print()" class="btn btn-secondary">
        <i class="bi bi-printer"></i> Print Report
    </button>
</div>

<!-- Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-funnel me-2"></i>Filter Reports
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="<?php echo $date_from; ?>">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="<?php echo $date_to; ?>">
            </div>
            <div class="col-md-3">
                <label for="class_id" class="form-label">Class</label>
                <select class="form-select" id="class_id" name="class_id">
                    <option value="">All My Classes</option>
                    <?php while ($class = mysqli_fetch_assoc($classes)): ?>
                    <option value="<?php echo $class['class_id']; ?>" 
                        <?php echo $class_id == $class['class_id'] ? 'selected' : ''; ?>>
                        <?php echo $class['class_name']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="present" <?php echo $status == 'present' ? 'selected' : ''; ?>>Present</option>
                    <option value="late" <?php echo $status == 'late' ? 'selected' : ''; ?>>Late</option>
                    <option value="absent" <?php echo $status == 'absent' ? 'selected' : ''; ?>>Absent</option>
                    <option value="excused" <?php echo $status == 'excused' ? 'selected' : ''; ?>>Excused</option>
                </select>
            </div>
            <div class="col-12 text-center mt-3">
                <button type="submit" class="btn btn-primary px-5">
                    <i class="bi bi-search me-2"></i>Generate Report
                </button>
                <a href="view-reports.php" class="btn btn-secondary px-5">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Reports Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-table me-2"></i>Attendance Records
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="reportsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($reports) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($reports)): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($row['date'])); ?></td>
                            <td><?php echo $row['student_code']; ?></td>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['class_name']; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $row['status'] == 'present' ? 'success' : 
                                         ($row['status'] == 'late' ? 'warning' : 
                                         ($row['status'] == 'excused' ? 'info' : 'danger')); 
                                ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $row['check_in_time'] ?? '-'; ?></td>
                            <td><?php echo $row['remarks'] ?? '-'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No attendance records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$page_scripts = '
<script>
$(document).ready(function() {
    $("#reportsTable").DataTable({
        pageLength: 25,
        order: [[0, "desc"]],
        responsive: true
    });
});
</script>';

include '../../includes/footer.php';
?>