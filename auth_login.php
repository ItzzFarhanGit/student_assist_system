<?php
session_start();
require_once __DIR__ . '/auth_db.php';

if (!isset($conn) || !$conn) {
    die("Database connection is not available.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role     = mysqli_real_escape_string($conn, $_POST['role']);

    $query  = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND role = '$role'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['role']      = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dash_index.php");
        }
        exit();
    } else {
        $error = "Invalid username, password, or role. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Assist</title>
    <link rel="stylesheet" href="auth_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="login-body">

<div class="login-wrapper">
    <div class="login-left">
        <h2>Welcome to...</h2>
        <div class="brand-box">
            <i class="fa-solid fa-book-open brand-icon"></i>
            <span class="brand-name">Student Assist</span>
        </div>
        <div class="login-image-container">
            <img src="auth_student.jpg" alt="Student studying" class="login-img">
        </div>
    </div>

    <div class="login-right">
        <h2 class="login-title">Login</h2>
        

       
                


        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="auth_login.php">
            <div class="form-group">
                <label for="username"><i class="fa-solid fa-user"></i> User Name</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="Enter username" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Password</label>
                <div class="pw-wrap">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Enter password" required>
                    <button type="button" class="pw-toggle" onclick="togglePw('password', this)" tabindex="-1">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="remember-row">
                <label class="remember-label">
                    <input type="checkbox" name="remember" id="remember"> Remember me
                </label>
                <a href="auth_forgot_password.php" class="forgot-link"><i class="fa-solid fa-key"></i> Forgot Password?</a>
            </div>

            <div class="role-select-row">
                <span class="role-select-label">Login as:</span>
                <div class="role-pills">
                    <label class="role-pill-btn" id="pill-student">
                        <input type="radio" name="role" value="student" id="role_student" checked
                               onchange="highlightPill(this)">
                        <span><i class="fa-solid fa-graduation-cap"></i> Student</span>
                    </label>
                    <label class="role-pill-btn" id="pill-admin">
                        <input type="radio" name="role" value="admin" id="role_admin"
                               onchange="highlightPill(this)">
                        <span><i class="fa-solid fa-user-shield"></i> Admin</span>
                    </label>
                </div>
            </div>

            <div class="role-login-row">
                <button type="submit" class="btn-login-full"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
            </div>

            <p class="register-row">Don't Have An Account? <a href="auth_register.php" class="register-link"><i class="fa-solid fa-user-plus"></i> Register</a></p>
        </form>
        <p class="site-credit" style="text-align:center; margin-top:18px; font-size:12px; color:#888;">
            &copy; <?php echo date('Y'); ?> Student Assist &mdash; Owned &amp; Built by Mohamed Farhan
        </p>
    </div>
</div>

<script>
function highlightPill(radio) {
    document.querySelectorAll('.role-pill-btn').forEach(function(el) {
        el.classList.remove('role-pill-active');
    });
    radio.parentElement.classList.add('role-pill-active');
}
window.onload = function() {
    var checked = document.querySelector('input[name="role"]:checked');
    if (checked) checked.parentElement.classList.add('role-pill-active');
};
function togglePw(inputId, btn) {
    var input = document.getElementById(inputId);
    var icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        btn.title = 'Hide password';
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        btn.title = 'Show password';
    }
}
</script>

</body>
</html>
