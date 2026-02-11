<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

if (!isTeacher()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$page = 'take-attendance';
$page_title = 'Take Attendance';

$teacher_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$selected_date = $_GET['date'] ?? $today;
$selected_class = $_GET['class_id'] ?? '';

// Get teacher's classes
$classes = mysqli_query($conn, 
    "SELECT * FROM classes WHERE teacher_id = $teacher_id ORDER BY class_name"
);

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_attendance'])) {
    $class_id = intval($_POST['class_id']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $marked_by = $_SESSION['user_id'];
    
    foreach ($_POST['attendance'] as $student_id => $status) {
        $student_id = intval($student_id);
        $check_in_time = ($status != 'absent') ? date('H:i:s') : null;
        $remarks = mysqli_real_escape_string($conn, $_POST['remarks'][$student_id] ?? '');
        
        $check = mysqli_query($conn, 
            "SELECT attendance_id FROM attendance 
             WHERE student_id = $student_id AND date = '$date' AND class_id = $class_id"
        );
        
        if (mysqli_num_rows($check) > 0) {
            $sql = "UPDATE attendance SET 
                    status = '$status',
                    check_in_time = " . ($check_in_time ? "'$check_in_time'" : "NULL") . ",
                    remarks = '$remarks',
                    marked_by = $marked_by
                    WHERE student_id = $student_id AND date = '$date' AND class_id = $class_id";
        } else {
            $sql = "INSERT INTO attendance (student_id, class_id, date, status, check_in_time, remarks, marked_by) 
                    VALUES ($student_id, $class_id, '$date', '$status', " . 
                    ($check_in_time ? "'$check_in_time'" : "NULL") . ", '$remarks', $marked_by)";
        }
        
        mysqli_query($conn, $sql);
    }
    
    $_SESSION['success'] = "Attendance saved successfully!";
    header("Location: take-attendance.php?class_id=$class_id&date=$date");
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

<!-- Class Selection -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-building me-2"></i>Select Class & Date
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
            <i class="bi bi-people me-2"></i>Mark Attendance - <?php echo date('d M Y', strtotime($selected_date)); ?>
        </h6>
        <div>
            <button type="button" class="btn btn-sm btn-success" onclick="markAll('present')">
                All Present
            </button>
            <button type="button" class="btn btn-sm btn-warning" onclick="markAll('late')">
                All Late
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="markAll('absent')">
                All Absent
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="attendanceForm">
            <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
            <input type="hidden" name="date" value="<?php echo $selected_date; ?>">
            
            <div class="table-responsive">
                <table class="table table-bordered" id="attendanceTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Status</th>
                            <th>Remarks</th>
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
                            <td><?php echo $student['full_name']; ?></td>
                            <td>
                                <select class="form-select" 
                                        name="attendance[<?php echo $student['student_id']; ?>]"
                                        onchange="updateRemarks(<?php echo $student['student_id']; ?>)">
                                    <option value="present" <?php echo $status == 'present' ? 'selected' : ''; ?>>
                                        ✓ Present
                                    </option>
                                    <option value="late" <?php echo $status == 'late' ? 'selected' : ''; ?>>
                                        ⏰ Late
                                    </option>
                                    <option value="absent" <?php echo $status == 'absent' ? 'selected' : ''; ?>>
                                        ✗ Absent
                                    </option>
                                    <option value="excused" <?php echo $status == 'excused' ? 'selected' : ''; ?>>
                                        ✓ Excused
                                    </option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" 
                                       name="remarks[<?php echo $student['student_id']; ?>]" 
                                       id="remarks_<?php echo $student['student_id']; ?>"
                                       placeholder="Add remarks"
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
    <i class="bi bi-info-circle me-2"></i>No students found in this class.
</div>
<?php endif; ?>

<?php
$page_scripts = '
<script>
function markAll(status) {
    var selects = document.querySelectorAll(\'select[name^="attendance"]\');
    selects.forEach(function(select) {
        select.value = status;
    });
}

function updateRemarks(studentId) {
    var status = document.querySelector(\'select[name="attendance[\' + studentId + \']"]\').value;
    var remarksField = document.getElementById("remarks_" + studentId);
    
    if (status === "absent") {
        remarksField.placeholder = "Reason for absence";
    } else if (status === "late") {
        remarksField.placeholder = "Minutes late";
    } else {
        remarksField.placeholder = "Optional remarks";
    }
}

$(document).ready(function() {
    $("#attendanceTable").DataTable({
        pageLength: 25,
        order: [[2, "asc"]]
    });
});
</script>';

include '../../includes/footer.php';
?>