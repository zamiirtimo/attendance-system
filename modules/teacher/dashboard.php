<?php
require_once '../../includes/config.php';  // 2 jeer kor u kac: modules/teacher/ -> modules/ -> root -> includes/
require_once '../../includes/auth.php';
requireLogin();

if (!isTeacher()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$page = 'dashboard';
$page_title = 'Teacher Dashboard';

$teacher_id = $_SESSION['user_id'];

// Get teacher's classes
$classes_query = mysqli_query($conn, 
    "SELECT * FROM classes WHERE teacher_id = $teacher_id ORDER BY class_name"
);
$total_classes = mysqli_num_rows($classes_query);

// Get total students in teacher's classes
$students_query = mysqli_query($conn,
    "SELECT COUNT(DISTINCT s.student_id) as total 
     FROM students s 
     JOIN classes c ON s.class_id = c.class_id 
     WHERE c.teacher_id = $teacher_id"
);
$total_students = mysqli_fetch_assoc($students_query)['total'];

// Today's attendance
$today = date('Y-m-d');
$attendance_query = mysqli_query($conn,
    "SELECT COUNT(*) as total 
     FROM attendance a 
     JOIN classes c ON a.class_id = c.class_id 
     WHERE c.teacher_id = $teacher_id AND a.date = '$today'"
);
$today_attendance = mysqli_fetch_assoc($attendance_query)['total'];

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="bi bi-speedometer2 me-2"></i>Teacher Dashboard
    </h1>
    <div>
        <span class="text-muted"><?php echo date('l, F d, Y'); ?></span>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            My Classes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_classes; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            My Students</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_students; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Today's Attendance</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $today_attendance; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- My Classes -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-list-ul me-2"></i>My Classes
                </h6>
                <a href="take-attendance.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Take Attendance
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="classesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Class Code</th>
                                <th>Class Name</th>
                                <th>Course</th>
                                <th>Semester</th>
                                <th>Students</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($class = mysqli_fetch_assoc($classes_query)): 
                                $student_count = mysqli_query($conn, 
                                    "SELECT COUNT(*) as total FROM students WHERE class_id = " . $class['class_id']
                                );
                                $count = mysqli_fetch_assoc($student_count);
                            ?>
                            <tr>
                                <td><strong><?php echo $class['class_code']; ?></strong></td>
                                <td><?php echo $class['class_name']; ?></td>
                                <td><?php echo $class['course_name']; ?></td>
                                <td><?php echo $class['semester'] ?? '-'; ?></td>
                                <td>
                                    <span class="badge bg-primary"><?php echo $count['total']; ?> Students</span>
                                </td>
                                <td>
                                    <a href="take-attendance.php?class_id=<?php echo $class['class_id']; ?>" 
                                       class="btn btn-sm btn-success">
                                        <i class="bi bi-clipboard-check"></i> Mark
                                    </a>
                                    <a href="view-reports.php?class_id=<?php echo $class['class_id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-file-text"></i> Reports
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$page_scripts = '
<script>
$(document).ready(function() {
    $("#classesTable").DataTable({
        pageLength: 10,
        order: [[0, "asc"]],
        responsive: true
    });
});
</script>';

include '../../includes/footer.php';
?>