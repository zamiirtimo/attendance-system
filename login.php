<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ' . BASE_URL . 'modules/admin/dashboard.php');
    } elseif ($_SESSION['user_role'] === 'teacher') {
        header('Location: ' . BASE_URL . 'modules/teacher/dashboard.php');
    }
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($auth->login($username, $password)) {
        if ($_SESSION['user_role'] === 'admin') {
            header('Location: ' . BASE_URL . 'modules/admin/dashboard.php');
        } elseif ($_SESSION['user_role'] === 'teacher') {
            header('Location: ' . BASE_URL . 'modules/teacher/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . 'login.php');
        }
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}

$page_title = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniAttend - University Attendance System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0c1a32 0%, #1a2f4f 50%, #0e1a2b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background Circles */
        .bg-circle {
            position: absolute;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            animation: float 20s infinite ease-in-out;
            pointer-events: none;
        }

        .circle1 {
            width: 500px;
            height: 500px;
            top: -200px;
            right: -100px;
            background: radial-gradient(circle, rgba(78,115,223,0.1) 0%, rgba(78,115,223,0) 70%);
            animation-delay: 0s;
        }

        .circle2 {
            width: 400px;
            height: 400px;
            bottom: -150px;
            left: -100px;
            background: radial-gradient(circle, rgba(118,75,162,0.1) 0%, rgba(118,75,162,0) 70%);
            animation-delay: -5s;
        }

        .circle3 {
            width: 300px;
            height: 300px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: radial-gradient(circle, rgba(255,193,7,0.05) 0%, rgba(255,193,7,0) 70%);
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-30px) scale(1.05); }
        }

        /* Main Container */
        .login-wrapper {
            width: 100%;
            max-width: 1200px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 50px;
            position: relative;
            z-index: 10;
        }

        /* Illustration Side */
        .illustration-side {
            flex: 1;
            display: none;
            color: white;
            padding: 40px;
        }

        @media (min-width: 992px) {
            .illustration-side {
                display: block;
                animation: fadeInLeft 1s ease-out;
            }
        }

        .illustration-content {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .illustration-icon {
            font-size: 80px;
            color: rgba(255,255,255,0.9);
            margin-bottom: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 30px;
            box-shadow: 0 20px 30px rgba(0,0,0,0.2);
        }

        .illustration-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .illustration-features {
            margin-top: 40px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
            padding: 10px;
            border-radius: 12px;
            background: rgba(255,255,255,0.03);
            transition: all 0.3s;
        }

        .feature-item:hover {
            background: rgba(255,255,255,0.08);
            transform: translateX(10px);
        }

        .feature-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        /* Login Card Side */
        .login-side {
            flex: 1;
            max-width: 450px;
            animation: fadeInRight 1s ease-out;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 40px;
            padding: 20px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
            transition: all 0.3s;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 40px 80px rgba(0,0,0,0.4);
        }

        .login-header {
            text-align: center;
            padding: 20px 20px 10px;
        }

        .logo-wrapper {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 15px 30px rgba(102,126,234,0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 15px 30px rgba(102,126,234,0.4); }
            50% { box-shadow: 0 20px 40px rgba(102,126,234,0.6); }
            100% { box-shadow: 0 15px 30px rgba(102,126,234,0.4); }
        }

        .logo-wrapper i {
            font-size: 40px;
            color: white;
        }

        .login-header h2 {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .login-header p {
            color: #6c757d;
            font-weight: 400;
            font-size: 14px;
        }

        /* Form Styles */
        .login-form {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #495057;
            margin-bottom: 8px;
            display: block;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 18px;
            z-index: 10;
        }

        .form-control {
            height: 55px;
            padding: 10px 20px 10px 50px;
            border: 2px solid #e9ecef;
            border-radius: 16px;
            font-size: 15px;
            transition: all 0.3s;
            background: white;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #adb5bd;
            font-weight: 300;
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        /* Login Button */
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 16px;
            padding: 16px;
            font-weight: 700;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: white;
            width: 100%;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(102,126,234,0.3);
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(102,126,234,0.4);
        }

        .btn-login i {
            margin-right: 8px;
            font-size: 18px;
            transition: all 0.3s;
        }

        .btn-login:hover i {
            transform: translateX(5px);
        }

        /* Demo Accounts Card */
        .demo-accounts {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            padding: 20px;
            margin-top: 30px;
            border: 1px solid rgba(255,255,255,0.5);
            animation: slideUp 1s ease-out;
        }

        .demo-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #495057;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .demo-items {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .demo-item {
            flex: 1;
            background: white;
            padding: 12px 16px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            border: 1px solid rgba(0,0,0,0.03);
            transition: all 0.3s;
        }

        .demo-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.05);
            border-color: #667eea;
        }

        .demo-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .demo-icon.admin {
            background: linear-gradient(135deg, #f54b64 0%, #f78361 100%);
            color: white;
        }

        .demo-icon.teacher {
            background: linear-gradient(135deg, #36b9cc 0%, #5a9bcf 100%);
            color: white;
        }

        .demo-info {
            flex: 1;
        }

        .demo-role {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6c757d;
        }

        .demo-username {
            font-weight: 700;
            color: #2d3748;
            font-size: 14px;
        }

        .demo-password {
            font-size: 11px;
            color: #6c757d;
        }

        /* Alert */
        .alert {
            border-radius: 16px;
            padding: 15px 20px;
            border: none;
            margin-bottom: 25px;
            animation: shake 0.5s ease-out;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fff5f5 0%, #fee 100%);
            color: #c53030;
            border-left: 6px solid #f56565;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        /* Animations */
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-side {
                max-width: 100%;
            }
            
            .login-card {
                padding: 15px;
            }
            
            .demo-items {
                flex-direction: column;
            }
        }

        /* Loading Spinner */
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-loading .btn-text {
            visibility: hidden;
        }

        .btn-loading::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <!-- Animated Background Circles -->
    <div class="bg-circle circle1"></div>
    <div class="bg-circle circle2"></div>
    <div class="bg-circle circle3"></div>

    <div class="login-wrapper">
        <!-- Illustration Side -->
        <div class="illustration-side">
            <div class="illustration-content">
                <div class="illustration-icon animate__animated animate__bounceIn">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <h1 class="illustration-title">Welcome to<br>UniAttend</h1>
                <p style="color: rgba(255,255,255,0.7); font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
                    Modern attendance management system designed for universities. Track, manage, and analyze student attendance with ease.
                </p>
                
                <div class="illustration-features">
                    <div class="feature-item animate__animated animate__fadeInLeft" style="animation-delay: 0.2s;">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <h5 style="color: white; margin-bottom: 5px; font-weight: 600;">Student Management</h5>
                            <p style="color: rgba(255,255,255,0.6); margin: 0; font-size: 13px;">Add, edit, and manage student records</p>
                        </div>
                    </div>
                    
                    <div class="feature-item animate__animated animate__fadeInLeft" style="animation-delay: 0.4s;">
                        <div class="feature-icon">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <div>
                            <h5 style="color: white; margin-bottom: 5px; font-weight: 600;">Attendance Tracking</h5>
                            <p style="color: rgba(255,255,255,0.6); margin: 0; font-size: 13px;">Mark present, absent, late with ease</p>
                        </div>
                    </div>
                    
                    <div class="feature-item animate__animated animate__fadeInLeft" style="animation-delay: 0.6s;">
                        <div class="feature-icon">
                            <i class="bi bi-bar-chart"></i>
                        </div>
                        <div>
                            <h5 style="color: white; margin-bottom: 5px; font-weight: 600;">Analytics & Reports</h5>
                            <p style="color: rgba(255,255,255,0.6); margin: 0; font-size: 13px;">Generate detailed attendance reports</p>
                        </div>
                    </div>
                    
                    <div class="feature-item animate__animated animate__fadeInLeft" style="animation-delay: 0.8s;">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <h5 style="color: white; margin-bottom: 5px; font-weight: 600;">Secure System</h5>
                            <p style="color: rgba(255,255,255,0.6); margin: 0; font-size: 13px;">Role-based access control</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Card Side -->
        <div class="login-side">
            <div class="login-card">
                <div class="login-header">
                    <div class="logo-wrapper animate__animated animate__zoomIn">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h2 class="animate__animated animate__fadeInUp">Welcome Back!</h2>
                    <p class="animate__animated animate__fadeInUp animate__delay-0.1s">Sign in to access your dashboard</p>
                </div>

                <div class="login-form">
                    <?php if ($error): ?>
                        <div class="alert alert-danger animate__animated animate__headShake">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="loginForm">
                        <div class="form-group animate__animated animate__fadeInUp animate__delay-0.2s">
                            <label for="username" class="form-label">
                                <i class="bi bi-person-circle me-1"></i>
                                Username
                            </label>
                            <div class="input-wrapper">
                                <span class="input-icon"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="Enter your username" required autofocus>
                            </div>
                        </div>

                        <div class="form-group animate__animated animate__fadeInUp animate__delay-0.3s">
                            <label for="password" class="form-label">
                                <i class="bi bi-shield-lock me-1"></i>
                                Password
                            </label>
                            <div class="input-wrapper">
                                <span class="input-icon"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter your password" required>
                                <span class="password-toggle" onclick="togglePassword()">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </span>
                            </div>
                        </div>

                        <div class="form-group animate__animated animate__fadeInUp animate__delay-0.4s">
                            <button type="submit" class="btn-login" id="loginBtn">
                                <i class="bi bi-box-arrow-in-right"></i>
                                <span class="btn-text">Sign In</span>
                            </button>
                        </div>
                    </form>

                    <!-- Demo Accounts -->
                    <div class="demo-accounts animate__animated animate__fadeInUp animate__delay-0.5s">
                        <div class="demo-title">
                            <i class="bi bi-info-circle-fill" style="color: #667eea;"></i>
                            Demo Access
                        </div>
                        <div class="demo-items">
                            <div class="demo-item">
                                <div class="demo-icon admin">
                                    <i class="bi bi-shield-fill"></i>
                                </div>
                                <div class="demo-info">
                                    <div class="demo-role">Administrator</div>
                                    <div class="demo-username">admin</div>
                                    <div class="demo-password">
                                        <i class="bi bi-key"></i> demo123
                                    </div>
                                </div>
                            </div>
                            <div class="demo-item">
                                <div class="demo-icon teacher">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <div class="demo-info">
                                    <div class="demo-role">Teacher</div>
                                    <div class="demo-username">teacher1</div>
                                    <div class="demo-password">
                                        <i class="bi bi-key"></i> demo123
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-center mt-4 mb-0 animate__animated animate__fadeIn animate__delay-0.6s" 
                       style="font-size: 12px; color: #6c757d;">
                        <i class="bi bi-shield-check me-1" style="color: #28a745;"></i>
                        Secured by UniAttend v1.0
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password Toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        // Form Loading Animation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('btn-loading');
        });

        // Auto-fill demo credentials (optional)
        function fillDemoCredentials(role) {
            if (role === 'admin') {
                document.getElementById('username').value = 'admin';
                document.getElementById('password').value = 'demo123';
            } else if (role === 'teacher') {
                document.getElementById('username').value = 'teacher1';
                document.getElementById('password').value = 'demo123';
            }
        }

        // Click on demo items to auto-fill
        document.querySelectorAll('.demo-item').forEach((item, index) => {
            item.addEventListener('click', function() {
                if (index === 0) {
                    fillDemoCredentials('admin');
                } else {
                    fillDemoCredentials('teacher');
                }
            });
        });

        // Floating animation for background circles
        document.addEventListener('mousemove', function(e) {
            const circles = document.querySelectorAll('.bg-circle');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            circles.forEach((circle, index) => {
                const speed = (index + 1) * 20;
                const xOffset = (x - 0.5) * speed;
                const yOffset = (y - 0.5) * speed;
                circle.style.transform = `translate(${xOffset}px, ${yOffset}px)`;
            });
        });

        // Add floating labels effect
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('.input-icon i').style.color = '#667eea';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('.input-icon i').style.color = '#667eea';
            });
        });
    </script>
</body>
</html>