<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

$page = 'attendance';
$page_title = 'Manage Attendance';

// Get today's date
$today = date('Y-m-d');
$selected_date = $_GET['date'] ?? $today;
$selected_class = $_GET['class_id'] ?? '';

// Get all classes for dropdown
$classes = mysqli_query($conn, "SELECT * FROM classes ORDER BY class_name");

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_attendance'])) {
    $class_id = intval($_POST['class_id']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $marked_by = $_SESSION['user_id'];
    
    foreach ($_POST['attendance'] as $student_id => $status) {
        $student_id = intval($student_id);
        $check_in_time = ($status != 'absent') ? date('H:i:s') : null;
        $remarks = mysqli_real_escape_string($conn, $_POST['remarks'][$student_id] ?? '');
        
        // Check if attendance already exists
        $check = mysqli_query($conn, 
            "SELECT attendance_id FROM attendance 
             WHERE student_id = $student_id AND date = '$date' AND class_id = $class_id"
        );
        
        if (mysqli_num_rows($check) > 0) {
            // Update existing attendance
            $sql = "UPDATE attendance SET 
                    status = '$status',
                    check_in_time = " . ($check_in_time ? "'$check_in_time'" : "NULL") . ",
                    remarks = '$remarks',
                    marked_by = $marked_by
                    WHERE student_id = $student_id AND date = '$date' AND class_id = $class_id";
        } else {
            // Insert new attendance
            $sql = "INSERT INTO attendance (student_id, class_id, date, status, check_in_time, remarks, marked_by) 
                    VALUES ($student_id, $class_id, '$date', '$status', " . 
                    ($check_in_time ? "'$check_in_time'" : "NULL") . ", '$remarks', $marked_by)";
        }
        
        mysqli_query($conn, $sql);
    }
    
    $_SESSION['success'] = "Attendance saved successfully for " . date('d M Y', strtotime($date));
    header("Location: attendance.php?class_id=$class_id&date=$date");
    exit();
}

// Get students for selected class
$students = [];
$attendance_data = [];
if ($selected_class) {
    $students = mysqli_query($conn, 
        "SELECT s.* FROM students s 
         WHERE s.class_id = $selected_class AND s.status = 'active'
         ORDER BY s.full_name"
    );
    
    // Get existing attendance for selected date and class
    $attendance = mysqli_query($conn,
        "SELECT * FROM attendance 
         WHERE class_id = $selected_class AND date = '$selected_date'"
    );
    
    while ($row = mysqli_fetch_assoc($attendance)) {
        $attendance_data[$row['student_id']] = $row;
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="bi bi-clipboard-check me-2"></i>Take Attendance
    </h1>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i><?php 
        echo $_SESSION['success']; 
        unset($_SESSION['success']);
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filter Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-funnel me-2"></i>Select Class & Date
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-5">
                <label for="class_id" class="form-label">Class</label>
                <select class="form-select" id="class_id" name="class_id" required>
                    <option value="">-- Select Class --</option>
                    <?php while ($class = mysqli_fetch_assoc($classes)): ?>
                    <option value="<?php echo $class['class_id']; ?>" 
                        <?php echo $selected_class == $class['class_id'] ? 'selected' : ''; ?>>
                        <?php echo $class['class_name'] . ' - ' . $class['course_name']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" 
                       value="<?php echo $selected_date; ?>" required>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-2"></i>Load Students
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($selected_class && mysqli_num_rows($students) > 0): ?>
<!-- Attendance Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-people me-2"></i>Student List - <?php echo date('d M Y', strtotime($selected_date)); ?>
        </h6>
        <div>
            <button type="button" class="btn btn-sm btn-success" onclick="markAll('present')">
                <i class="bi bi-check-all"></i> All Present
            </button>
            <button type="button" class="btn btn-sm btn-warning" onclick="markAll('late')">
                <i class="bi bi-clock"></i> All Late
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="markAll('absent')">
                <i class="bi bi-x-circle"></i> All Absent
            </button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="markAll('none')">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="attendanceForm">
            <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
            <input type="hidden" name="date" value="<?php echo $selected_date; ?>">
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="attendanceTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Student ID</th>
                            <th width="25%">Student Name</th>
                            <th width="30%">Attendance Status</th>
                            <th width="25%">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        while ($student = mysqli_fetch_assoc($students)): 
                            $existing = $attendance_data[$student['student_id']] ?? null;
                            $status = $existing['status'] ?? '';
                        ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo $student['student_code']; ?></td>
                            <td>
                                <strong><?php echo $student['full_name']; ?></strong>
                                <br>
                                <small class="text-muted"><?php echo $student['email']; ?></small>
                            </td>
                            <td>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" 
                                           name="attendance[<?php echo $student['student_id']; ?>]" 
                                           id="present_<?php echo $student['student_id']; ?>" 
                                           value="present" 
                                           <?php echo $status == 'present' ? 'checked' : ''; ?>
                                           onchange="updateRemarks(<?php echo $student['student_id']; ?>)">
                                    <label class="btn btn-outline-success attendance-btn" 
                                           for="present_<?php echo $student['student_id']; ?>">
                                        <i class="bi bi-check-circle"></i> Present
                                    </label>
                                    
                                    <input type="radio" class="btn-check" 
                                           name="attendance[<?php echo $student['student_id']; ?>]" 
                                           id="late_<?php echo $student['student_id']; ?>" 
                                           value="late"
                                           <?php echo $status == 'late' ? 'checked' : ''; ?>
                                           onchange="updateRemarks(<?php echo $student['student_id']; ?>)">
                                    <label class="btn btn-outline-warning attendance-btn" 
                                           for="late_<?php echo $student['student_id']; ?>">
                                        <i class="bi bi-clock"></i> Late
                                    </label>
                                    
                                    <input type="radio" class="btn-check" 
                                           name="attendance[<?php echo $student['student_id']; ?>]" 
                                           id="absent_<?php echo $student['student_id']; ?>" 
                                           value="absent"
                                           <?php echo $status == 'absent' ? 'checked' : ''; ?>
                                           onchange="updateRemarks(<?php echo $student['student_id']; ?>)">
                                    <label class="btn btn-outline-danger attendance-btn" 
                                           for="absent_<?php echo $student['student_id']; ?>">
                                        <i class="bi bi-x-circle"></i> Absent
                                    </label>
                                    
                                    <input type="radio" class="btn-check" 
                                           name="attendance[<?php echo $student['student_id']; ?>]" 
                                           id="excused_<?php echo $student['student_id']; ?>" 
                                           value="excused"
                                           <?php echo $status == 'excused' ? 'checked' : ''; ?>
                                           onchange="updateRemarks(<?php echo $student['student_id']; ?>)">
                                    <label class="btn btn-outline-info attendance-btn" 
                                           for="excused_<?php echo $student['student_id']; ?>">
                                        <i class="bi bi-check2-circle"></i> Excused
                                    </label>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" 
                                       name="remarks[<?php echo $student['student_id']; ?>]" 
                                       id="remarks_<?php echo $student['student_id']; ?>"
                                       placeholder="Optional remarks"
                                       value="<?php echo $existing['remarks'] ?? ''; ?>">
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" name="save_attendance" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-save me-2"></i>Save Attendance
                </button>
            </div>
        </form>
    </div>
</div>
<?php elseif ($selected_class): ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>No active students found in this class.
</div>
<?php endif; ?>

<!-- Attendance Summary -->
<?php if ($selected_class && isset($attendance_data)): 
    $total_students = mysqli_num_rows($students);
    $present_count = count(array_filter($attendance_data, function($a) { return $a['status'] == 'present'; }));
    $late_count = count(array_filter($attendance_data, function($a) { return $a['status'] == 'late'; }));
    $absent_count = count(array_filter($attendance_data, function($a) { return $a['status'] == 'absent'; }));
    $excused_count = count(array_filter($attendance_data, function($a) { return $a['status'] == 'excused'; }));
?>
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-success text-white shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Present</div>
                        <div class="h3 mb-0"><?php echo $present_count; ?></div>
                    </div>
                    <i class="bi bi-check-circle fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Late</div>
                        <div class="h3 mb-0"><?php echo $late_count; ?></div>
                    </div>
                    <i class="bi bi-clock fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Absent</div>
                        <div class="h3 mb-0"><?php echo $absent_count; ?></div>
                    </div>
                    <i class="bi bi-x-circle fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Excused</div>
                        <div class="h3 mb-0"><?php echo $excused_count; ?></div>
                    </div>
                    <i class="bi bi-check2-circle fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$page_scripts = '
<script>
function markAll(status) {
    var radios = document.querySelectorAll(\'input[type="radio"]\');
    radios.forEach(function(radio) {
        if (status === "none") {
            radio.checked = false;
        } else if (radio.value === status) {
            radio.checked = true;
        }
    });
}

function updateRemarks(studentId) {
    var status = document.querySelector(\'input[name="attendance[\' + studentId + \']"]:checked\');
    var remarksField = document.getElementById("remarks_" + studentId);
    
    if (status) {
        if (status.value === "absent") {
            remarksField.placeholder = "Reason for absence";
            remarksField.focus();
        } else if (status.value === "late") {
            remarksField.placeholder = "Minutes late / reason";
        } else {
            remarksField.placeholder = "Optional remarks";
        }
    }
}

$(document).ready(function() {
    $("#attendanceTable").DataTable({
        pageLength: 25,
        order: [[2, "asc"]],
        responsive: true
    });
});
</script>';

include '../../includes/footer.php';
?>