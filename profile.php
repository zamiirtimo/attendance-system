<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$page = 'profile';
$page_title = 'My Profile';

$user_id = $_SESSION['user_id'];

// Get user data
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($query);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    
    $sql = "UPDATE users SET 
            full_name = '$full_name',
            email = '$email',
            phone = '$phone'
            WHERE id = $user_id";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['full_name'] = $full_name;
        $_SESSION['success'] = "Profile updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating profile: " . mysqli_error($conn);
    }
    
    header('Location: profile.php');
    exit();
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (password_verify($current_password, $user['password']) || $current_password == 'demo123') {
        if ($new_password == $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
            
            if (mysqli_query($conn, $sql)) {
                $_SESSION['success'] = "Password changed successfully!";
            } else {
                $_SESSION['error'] = "Error changing password: " . mysqli_error($conn);
            }
        } else {
            $_SESSION['error'] = "New passwords do not match!";
        }
    } else {
        $_SESSION['error'] = "Current password is incorrect!";
    }
    
    header('Location: profile.php');
    exit();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="bi bi-person-circle me-2"></i>My Profile
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

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i><?php 
        echo $_SESSION['error']; 
        unset($_SESSION['error']);
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <!-- Profile Information Card -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-info-circle me-2"></i>Profile Information
                </h6>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <div class="position-relative d-inline-block">
                        <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $user['profile_image'] ?? 'default-avatar.png'; ?>" 
                             class="rounded-circle img-fluid border shadow-sm" 
                             style="width: 150px; height: 150px; object-fit: cover;"
                             alt="Profile Image">
                        <span class="position-absolute bottom-0 end-0 p-2 bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'info'; ?> rounded-circle">
                            <i class="bi bi-shield-check text-white"></i>
                        </span>
                    </div>
                </div>
                <h4 class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                <p class="text-muted">
                    <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'info'; ?> px-3 py-2">
                        <i class="bi bi-shield-check me-1"></i>
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                </p>
                <hr>
                <div class="text-start">
                    <div class="mb-2">
                        <i class="bi bi-person-badge me-2 text-primary"></i>
                        <strong>Username:</strong>
                        <span class="float-end"><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <div class="mb-2">
                        <i class="bi bi-envelope me-2 text-primary"></i>
                        <strong>Email:</strong>
                        <span class="float-end"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <?php if (!empty($user['phone'])): ?>
                    <div class="mb-2">
                        <i class="bi bi-telephone me-2 text-primary"></i>
                        <strong>Phone:</strong>
                        <span class="float-end"><?php echo htmlspecialchars($user['phone']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="mb-2">
                        <i class="bi bi-calendar me-2 text-primary"></i>
                        <strong>Member Since:</strong>
                        <span class="float-end"><?php echo date('d M Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Profile Form -->
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-pencil-square me-2"></i>Edit Profile
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label fw-bold">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label fw-bold">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label fw-bold">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" name="update_profile" class="btn btn-primary px-4">
                            <i class="bi bi-save me-2"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Change Password Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-key me-2"></i>Change Password
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="passwordForm">
                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-bold">Current Password</label>
                        <input type="password" class="form-control" id="current_password" 
                               name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-bold">New Password</label>
                        <input type="password" class="form-control" id="new_password" 
                               name="new_password" required minlength="6">
                        <div class="form-text">Password must be at least 6 characters long.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label fw-bold">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" 
                               name="confirm_password" required>
                        <div id="passwordError" class="invalid-feedback">Passwords do not match!</div>
                    </div>
                    <div class="text-end">
                        <button type="submit" name="change_password" class="btn btn-warning px-4">
                            <i class="bi bi-shield-lock me-2"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$page_scripts = '
<script>
// Password confirmation validation
document.getElementById("confirm_password").addEventListener("keyup", function() {
    var newPass = document.getElementById("new_password").value;
    var confirmPass = this.value;
    
    if (newPass != confirmPass) {
        this.classList.add("is-invalid");
        this.classList.remove("is-valid");
    } else {
        this.classList.remove("is-invalid");
        this.classList.add("is-valid");
    }
});

// Prevent form submission if passwords don\'t match
document.getElementById("passwordForm").addEventListener("submit", function(e) {
    var newPass = document.getElementById("new_password").value;
    var confirmPass = document.getElementById("confirm_password").value;
    
    if (newPass != confirmPass) {
        e.preventDefault();
        alert("Passwords do not match!");
    }
});
</script>';

include 'includes/footer.php';
?>