<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireLogin();

if (!isAdmin()) {
    header('Location: ../dashboard.php');
    exit();
}

$page = 'classes';
$page_title = 'Manage Classes';

// Handle Add Class
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_class'])) {
    $class_name = mysqli_real_escape_string($conn, $_POST['class_name']);
    $class_code = mysqli_real_escape_string($conn, $_POST['class_code']);
    $course_name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $teacher_id = !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 'NULL';
    
    $sql = "INSERT INTO classes (class_name, class_code, course_name, semester, teacher_id) 
            VALUES ('$class_name', '$class_code', '$course_name', '$semester', $teacher_id)";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Class added successfully!";
    } else {
        $_SESSION['error'] = "Error adding class: " . mysqli_error($conn);
    }
    header('Location: manage-classes.php');
    exit();
}

// Handle Edit Class
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_class'])) {
    $class_id = intval($_POST['class_id']);
    $class_name = mysqli_real_escape_string($conn, $_POST['class_name']);
    $class_code = mysqli_real_escape_string($conn, $_POST['class_code']);
    $course_name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $teacher_id = !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 'NULL';
    
    $sql = "UPDATE classes SET 
            class_name = '$class_name',
            class_code = '$class_code',
            course_name = '$course_name',
            semester = '$semester',
            teacher_id = $teacher_id
            WHERE class_id = $class_id";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Class updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating class: " . mysqli_error($conn);
    }
    header('Location: manage-classes.php');
    exit();
}

// Handle Delete Class
if (isset($_GET['delete'])) {
    $class_id = intval($_GET['delete']);
    
    // Check if class has students
    $check = mysqli_query($conn, "SELECT COUNT(*) as total FROM students WHERE class_id = $class_id");
    $result = mysqli_fetch_assoc($check);
    
    if ($result['total'] > 0) {
        $_SESSION['error'] = "Cannot delete class with assigned students!";
    } else {
        $sql = "DELETE FROM classes WHERE class_id = $class_id";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Class deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting class: " . mysqli_error($conn);
        }
    }
    header('Location: manage-classes.php');
    exit();
}

// Get all classes with teacher info
$classes = mysqli_query($conn, 
    "SELECT c.*, u.full_name as teacher_name 
     FROM classes c 
     LEFT JOIN users u ON c.teacher_id = u.id AND u.role = 'teacher'
     ORDER BY c.class_name");

// Get all teachers for dropdown
$teachers = mysqli_query($conn, "SELECT * FROM users WHERE role = 'teacher' AND status = 'active'");

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="bi bi-building me-2"></i>Manage Classes
    </h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
        <i class="bi bi-plus-circle me-1"></i>Add New Class
    </button>
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

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i><?php 
        echo $_SESSION['error']; 
        unset($_SESSION['error']);
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Classes List -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-list-ul me-2"></i>All Classes
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="classesTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Class Code</th>
                        <th>Class Name</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>Teacher</th>
                        <th>Students</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($class = mysqli_fetch_assoc($classes)): 
                        // Get student count
                        $count_query = mysqli_query($conn, 
                            "SELECT COUNT(*) as total FROM students WHERE class_id = " . $class['class_id']);
                        $student_count = mysqli_fetch_assoc($count_query);
                    ?>
                    <tr>
                        <td><?php echo $class['class_id']; ?></td>
                        <td><strong><?php echo $class['class_code']; ?></strong></td>
                        <td><?php echo $class['class_name']; ?></td>
                        <td><?php echo $class['course_name']; ?></td>
                        <td><?php echo $class['semester'] ?? '-'; ?></td>
                        <td>
                            <?php if ($class['teacher_name']): ?>
                                <span class="badge bg-info"><?php echo $class['teacher_name']; ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Not Assigned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-primary"><?php echo $student_count['total']; ?> Students</span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($class['created_at'])); ?></td>
                        <td>
                            <button onclick="editClass(<?php echo $class['class_id']; ?>)" 
                                    class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editClassModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="?delete=<?php echo $class['class_id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this class?')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <a href="../attendance.php?class_id=<?php echo $class['class_id']; ?>" 
                               class="btn btn-sm btn-success">
                                <i class="bi bi-clipboard-check"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Class Modal -->
<div class="modal fade" id="addClassModal" tabindex="-1" aria-labelledby="addClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClassModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Add New Class
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="class_name" class="form-label">Class Name *</label>
                            <input type="text" class="form-control" id="class_name" name="class_name" 
                                   placeholder="e.g., Computer Science 101" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="class_code" class="form-label">Class Code *</label>
                            <input type="text" class="form-control" id="class_code" name="class_code" 
                                   placeholder="e.g., CS101" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="course_name" class="form-label">Course Name *</label>
                            <input type="text" class="form-control" id="course_name" name="course_name" 
                                   placeholder="e.g., Introduction to Programming" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="semester" class="form-label">Semester</label>
                            <input type="text" class="form-control" id="semester" name="semester" 
                                   placeholder="e.g., Fall 2024">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="teacher_id" class="form-label">Assign Teacher</label>
                            <select class="form-select" id="teacher_id" name="teacher_id">
                                <option value="">-- Not Assigned --</option>
                                <?php while ($teacher = mysqli_fetch_assoc($teachers)): ?>
                                <option value="<?php echo $teacher['id']; ?>">
                                    <?php echo $teacher['full_name']; ?> (<?php echo $teacher['email']; ?>)
                                </option>
                                <?php 
                                endwhile;
                                mysqli_data_seek($teachers, 0);
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_class" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Save Class
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Class Modal -->
<div class="modal fade" id="editClassModal" tabindex="-1" aria-labelledby="editClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClassModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Edit Class
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="class_id" id="edit_class_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_class_name" class="form-label">Class Name *</label>
                            <input type="text" class="form-control" id="edit_class_name" name="class_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_class_code" class="form-label">Class Code *</label>
                            <input type="text" class="form-control" id="edit_class_code" name="class_code" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_course_name" class="form-label">Course Name *</label>
                            <input type="text" class="form-control" id="edit_course_name" name="course_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_semester" class="form-label">Semester</label>
                            <input type="text" class="form-control" id="edit_semester" name="semester">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="edit_teacher_id" class="form-label">Assign Teacher</label>
                            <select class="form-select" id="edit_teacher_id" name="teacher_id">
                                <option value="">-- Not Assigned --</option>
                                <?php 
                                mysqli_data_seek($teachers, 0);
                                while ($teacher = mysqli_fetch_assoc($teachers)): 
                                ?>
                                <option value="<?php echo $teacher['id']; ?>">
                                    <?php echo $teacher['full_name']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_class" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Update Class
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$page_scripts = '
<script>
$(document).ready(function() {
    $("#classesTable").DataTable({
        pageLength: 10,
        order: [[0, "desc"]],
        responsive: true
    });
});

function editClass(classId) {
    // Fetch class data via AJAX
    $.ajax({
        url: "../../ajax/get-class.php",
        method: "GET",
        data: {id: classId},
        dataType: "json",
        success: function(data) {
            $("#edit_class_id").val(data.class_id);
            $("#edit_class_name").val(data.class_name);
            $("#edit_class_code").val(data.class_code);
            $("#edit_course_name").val(data.course_name);
            $("#edit_semester").val(data.semester);
            $("#edit_teacher_id").val(data.teacher_id);
        },
        error: function(xhr, status, error) {
            console.error(error);
            alert("Error loading class data");
        }
    });
}
</script>';

include '../../includes/footer.php';
?>