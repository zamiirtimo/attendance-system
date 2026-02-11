<?php
require_once 'config.php';

class Auth {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function login($username, $password) {
        $username = mysqli_real_escape_string($this->conn, $username);
        
        $sql = "SELECT * FROM users WHERE username = ? AND status = 'active'";
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($user = mysqli_fetch_assoc($result)) {
                // Check password - verify hashed password or demo password
                if (password_verify($password, $user['password']) || $password == 'demo123') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['profile_image'] = $user['profile_image'] ?? 'default-avatar.png';
                    return true;
                }
            }
        }
        return false;
    }
    
    public function logout() {
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
    
    public function getCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                return mysqli_fetch_assoc($result);
            }
        }
        return null;
    }
    
    public function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    public function isTeacher() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'teacher';
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

$auth = new Auth($conn);
?>