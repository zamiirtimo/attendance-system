<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

if (!isAdmin()) {
    header('Location: ' . BASE_URL . 'modules/teacher/dashboard.php');
    exit();
}

$page = 'students';
$page_title = 'Student Management';

// ============================================
// HANDLE ADD STUDENT
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $student_code = mysqli_real_escape_string($conn, $_POST['student_code']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $class_id = !empty($_POST['class_id']) ? intval($_POST['class_id']) : 'NULL';
    $enrollment_date = mysqli_real_escape_string($conn, $_POST['enrollment_date'] ?? date('Y-m-d'));
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'active');
    
    $errors = [];
    
    if (empty($student_code)) $errors[] = "Student Code is required";
    if (empty($full_name)) $errors[] = "Full Name is required";
    if (empty($email)) $errors[] = "Email is required";
    
    // Check if student code exists
    $check_code = mysqli_query($conn, "SELECT student_id FROM students WHERE student_code = '$student_code'");
    if (mysqli_num_rows($check_code) > 0) $errors[] = "Student Code already exists";
    
    // Check if email exists
    $check_email = mysqli_query($conn, "SELECT student_id FROM students WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) $errors[] = "Email already exists";
    
    if (empty($errors)) {
        $sql = "INSERT INTO students (student_code, full_name, email, phone, class_id, enrollment_date, status) 
                VALUES ('$student_code', '$full_name', '$email', '$phone', $class_id, '$enrollment_date', '$status')";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Student added successfully!";
        } else {
            $_SESSION['error'] = "Error adding student: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['errors'] = $errors;
    }
    
    header('Location: students.php');
    exit();
}

// ============================================
// HANDLE EDIT STUDENT
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_student'])) {
    $student_id = intval($_POST['student_id']);
    $student_code = mysqli_real_escape_string($conn, $_POST['student_code']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $class_id = !empty($_POST['class_id']) ? intval($_POST['class_id']) : 'NULL';
    $enrollment_date = mysqli_real_escape_string($conn, $_POST['enrollment_date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Check student code for other students
    $check_code = mysqli_query($conn, "SELECT student_id FROM students WHERE student_code = '$student_code' AND student_id != $student_id");
    if (mysqli_num_rows($check_code) > 0) {
        $_SESSION['error'] = "Student Code already exists";
        header('Location: students.php');
        exit();
    }
    
    // Check email for other students
    $check_email = mysqli_query($conn, "SELECT student_id FROM students WHERE email = '$email' AND student_id != $student_id");
    if (mysqli_num_rows($check_email) > 0) {
        $_SESSION['error'] = "Email already exists";
        header('Location: students.php');
        exit();
    }
    
    $sql = "UPDATE students SET 
            student_code = '$student_code',
            full_name = '$full_name',
            email = '$email',
            phone = '$phone',
            class_id = $class_id,
            enrollment_date = '$enrollment_date',
            status = '$status'
            WHERE student_id = $student_id";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Student updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating student: " . mysqli_error($conn);
    }
    
    header('Location: students.php');
    exit();
}

// ============================================
// HANDLE DELETE STUDENT - SAXAN!
// ============================================
if (isset($_GET['delete'])) {
    $student_id = intval($_GET['delete']);
    
    // Step 1: Delete attendance records first (foreign key constraint)
    $delete_attendance = mysqli_query($conn, "DELETE FROM attendance WHERE student_id = $student_id");
    
    if ($delete_attendance) {
        // Step 2: Then delete the student
        $sql = "DELETE FROM students WHERE student_id = $student_id";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Student and all attendance records deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting student: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Error deleting attendance records: " . mysqli_error($conn);
    }
    
    header('Location: students.php');
    exit();
}

// ============================================
// GET STUDENT FOR EDIT
// ============================================
$edit_student = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_query = mysqli_query($conn, "SELECT * FROM students WHERE student_id = $edit_id");
    $edit_student = mysqli_fetch_assoc($edit_query);
}

// ============================================
// GET ALL STUDENTS
// ============================================
$students = mysqli_query($conn, 
    "SELECT s.*, c.class_name, c.class_code 
     FROM students s 
     LEFT JOIN classes c ON s.class_id = c.class_id 
     ORDER BY s.created_at DESC");

// ============================================
// GET ALL CLASSES
// ============================================
$classes = mysqli_query($conn, "SELECT * FROM classes ORDER BY class_name");

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="bi bi-people me-2"></i>Student Management
    </h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="bi bi-plus-circle me-1"></i>Add New Student
    </button>
</div>

<!-- Alert Messages -->
<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['errors'])): ?>
<div class="alert alert-danger">
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0 mt-2">
        <?php foreach ($_SESSION['errors'] as $error): ?>
            <li><?php echo $error; ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<!-- Students Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-list-ul me-2"></i>All Students
        </h6>
        <span class="badge bg-primary"><?php echo mysqli_num_rows($students); ?> Total Students</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="studentsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Code</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Class</th>
                        <th>Enrollment Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = mysqli_fetch_assoc($students)): ?>
                    <tr>
                        <td><?php echo $student['student_id']; ?></td>
                        <td><strong><?php echo $student['student_code']; ?></strong></td>
                        <td><?php echo $student['full_name']; ?></td>
                        <td><?php echo $student['email']; ?></td>
                        <td><?php echo $student['phone'] ?? '-'; ?></td>
                        <td><?php echo $student['class_name'] ?? 'Not Assigned'; ?></td>
                        <td><?php echo date('d M Y', strtotime($student['enrollment_date'])); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $student['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($student['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="?edit=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editStudentModal">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="?delete=<?php echo $student['student_id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('WARNING: This will delete the student AND all their attendance records! Are you sure?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Student Code *</label>
                            <input type="text" class="form-control" name="student_code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class</label>
                            <select class="form-control" name="class_id">
                                <option value="">-- Select Class --</option>
                                <?php 
                                mysqli_data_seek($classes, 0);
                                while ($class = mysqli_fetch_assoc($classes)): 
                                ?>
                                <option value="<?php echo $class['class_id']; ?>">
                                    <?php echo $class['class_name'] . ' - ' . $class['class_code']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Enrollment Date</label>
                            <input type="date" class="form-control" name="enrollment_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<?php if ($edit_student): ?>
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true" style="display: block;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Student</h5>
                    <a href="students.php" class="btn-close"></a>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="student_id" value="<?php echo $edit_student['student_id']; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Student Code *</label>
                            <input type="text" class="form-control" name="student_code" value="<?php echo $edit_student['student_code']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="full_name" value="<?php echo $edit_student['full_name']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" value="<?php echo $edit_student['email']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?php echo $edit_student['phone'] ?? ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class</label>
                            <select class="form-control" name="class_id">
                                <option value="">-- Select Class --</option>
                                <?php 
                                mysqli_data_seek($classes, 0);
                                while ($class = mysqli_fetch_assoc($classes)): 
                                ?>
                                <option value="<?php echo $class['class_id']; ?>" <?php echo $edit_student['class_id'] == $class['class_id'] ? 'selected' : ''; ?>>
                                    <?php echo $class['class_name'] . ' - ' . $class['class_code']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Enrollment Date</label>
                            <input type="date" class="form-control" name="enrollment_date" value="<?php echo $edit_student['enrollment_date']; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="active" <?php echo $edit_student['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="graduated" <?php echo $edit_student['status'] == 'graduated' ? 'selected' : ''; ?>>Graduated</option>
                                <option value="left" <?php echo $edit_student['status'] == 'left' ? 'selected' : ''; ?>>Left</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="students.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="edit_student" class="btn btn-primary">Update Student</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$page_scripts = '
<script>
$(document).ready(function() {
    $("#studentsTable").DataTable({
        pageLength: 10,
        order: [[0, "desc"]],
        responsive: true
    });
});
</script>';

include '../../includes/footer.php';
?>