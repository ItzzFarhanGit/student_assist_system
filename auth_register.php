<?php
session_start();
require_once __DIR__ . '/auth_db.php';

if (!isset($conn) || !$conn) {
    die("Database connection is not available.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username  = mysqli_real_escape_string($conn, $_POST['username']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $password  = mysqli_real_escape_string($conn, $_POST['password']);
    $role      = mysqli_real_escape_string($conn, $_POST['role']);

    // Check if username already exists
    $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' OR email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username or email already exists.";
    } else {
        mysqli_query($conn, "INSERT INTO users (username, email, password, role, full_name) VALUES ('$username','$email','$password','$role','$full_name')");
        $success = "Account created successfully! <a href='auth_login.php'>Login now</a>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Student Assist</title>
    <link rel="stylesheet" href="auth_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="auth-body">

<div class="auth-card" style="max-width:420px;">
    <div class="auth-brand">
        <i class="fa-solid fa-book-open brand-icon-sm" style="color:#2563eb;"></i>
        <span class="auth-brand-name">Student Assist</span>
    </div>
    <hr class="auth-divider">
    <h3 class="auth-card-title"><i class="fa-solid fa-user-plus"></i> Create Account</h3>

    <?php if ($error): ?><div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div><?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label><i class="fa-solid fa-id-card"></i> Full Name</label>
            <input type="text" name="full_name" class="form-input" placeholder="Your full name" required>
        </div>
        <div class="form-group">
            <label><i class="fa-solid fa-user"></i> Username</label>
            <input type="text" name="username" class="form-input" placeholder="Choose a username" required>
        </div>
        <div class="form-group">
            <label><i class="fa-solid fa-envelope"></i> Email</label>
            <input type="email" name="email" class="form-input" placeholder="Your email address" required>
        </div>
        <div class="form-group">
            <label><i class="fa-solid fa-lock"></i> Password</label>
            <div class="pw-wrap">
                <input type="password" id="reg_password" name="password" class="form-input" placeholder="Create a password" required>
                <button type="button" class="pw-toggle" onclick="togglePw('reg_password', this)" tabindex="-1" title="Show password">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </div>
        <div class="form-group">
            <label><i class="fa-solid fa-user-tag"></i> Register as</label>
            <select name="role" class="form-input" style="border-radius:8px;">
                <option value="student">🎓 Student</option>
                
            </select>
        </div>
        <button type="submit" class="btn-blue-full">
            <i class="fa-solid fa-right-to-bracket"></i> Create Account
        </button>
        <p style="text-align:center; margin-top:12px; font-size:13px;">
            Already have an account? <a href="auth_login.php" class="register-link"><i class="fa-solid fa-arrow-left"></i> Login</a>
        </p>
    </form>
</div>

<script>
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
