<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$page = 'reports';
$page_title = 'Attendance Reports';

// Get filter parameters
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$class_id = $_GET['class_id'] ?? '';
$student_id = $_GET['student_id'] ?? '';
$status = $_GET['status'] ?? '';

// Get classes for dropdown
$classes = mysqli_query($conn, "SELECT * FROM classes ORDER BY class_name");

// Build query based on filters
$query = "SELECT a.*, s.full_name, s.student_code, c.class_name, c.course_name, u.full_name as marked_by_name
          FROM attendance a
          JOIN students s ON a.student_id = s.student_id
          JOIN classes c ON a.class_id = c.class_id
          LEFT JOIN users u ON a.marked_by = u.id
          WHERE a.date BETWEEN '$date_from' AND '$date_to'";

if (!empty($class_id)) {
    $query .= " AND a.class_id = " . intval($class_id);
}

if (!empty($student_id)) {
    $query .= " AND a.student_id = " . intval($student_id);
}

if (!empty($status)) {
    $query .= " AND a.status = '$status'";
}

$query .= " ORDER BY a.date DESC, c.class_name, s.full_name";

$reports = mysqli_query($conn, $query);

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as total_present,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as total_late,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as total_absent,
                SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as total_excused
                FROM attendance 
                WHERE date BETWEEN '$date_from' AND '$date_to'";

if (!empty($class_id)) {
    $stats_query .= " AND class_id = " . intval($class_id);
}

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="bi bi-file-earmark-bar-graph me-2"></i>Attendance Reports
    </h1>
    <div>
        <button onclick="window.print()" class="btn btn-secondary me-2">
            <i class="bi bi-printer"></i> Print
        </button>
        <button onclick="exportToExcel()" class="btn btn-success">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
        </button>
    </div>
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
                    <option value="">All Classes</option>
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
                <a href="reports.php" class="btn btn-secondary px-5">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Records</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['total_records'] ?? 0; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-database fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Present</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['total_present'] ?? 0; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Late</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['total_late'] ?? 0; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Absent</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['total_absent'] ?? 0; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-x-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Excused</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $stats['total_excused'] ?? 0; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check2-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
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
        <div class="table-responsive" id="reportContent">
            <table class="table table-bordered table-hover" id="reportsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th>Remarks</th>
                        <th>Marked By</th>
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
                            <td><?php echo $row['marked_by_name'] ?? 'System'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No attendance records found</td>
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

function exportToExcel() {
    var date = new Date();
    var filename = "attendance_report_" + date.getTime() + ".xls";
    
    var html = document.getElementById("reportContent").innerHTML;
    var css = \'<style> \
        table {border-collapse: collapse; width: 100%;} \
        th, td {border: 1px solid #ddd; padding: 8px;} \
        th {background-color: #4e73df; color: white;} \
    </style>\';
    
    var win = window.open("about:blank", "_blank");
    win.document.write("<html><head>" + css + "</head><body>" + html + "</body></html>");
    win.document.close();
    win.focus();
    win.print();
}
</script>';

include '../../includes/footer.php';
?>