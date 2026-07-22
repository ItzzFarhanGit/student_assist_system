<?php
session_start();
require_once __DIR__ . '/auth_db.php';

if (!isset($conn) || !$conn) {
    die("Database connection is not available.");
}

if (!isset($_SESSION['user_id'])) {
    header("Location: auth_login.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$role     = $_SESSION['role'];
$success  = '';
$error    = '';

$res  = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($res);

if (isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email     = mysqli_real_escape_string($conn, trim($_POST['email']));

    if (empty($full_name) || empty($email)) {
        $error = "Name and email cannot be empty.";
    } else {
        // Check email not taken by another user
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != $user_id");
        if (mysqli_num_rows($check) > 0) {
            $error = "That email is already used by another account.";
        } else {
            mysqli_query($conn, "UPDATE users SET full_name = '$full_name', email = '$email' WHERE id = $user_id");
            $_SESSION['full_name'] = $full_name;
            $success = "Profile updated successfully!";
            // Refresh user data
            $res  = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
            $user = mysqli_fetch_assoc($res);
        }
    }
}

if (isset($_POST['change_password'])) {
    $current  = mysqli_real_escape_string($conn, $_POST['current_pass']);
    $new_pass = mysqli_real_escape_string($conn, $_POST['new_pass']);
    $confirm  = mysqli_real_escape_string($conn, $_POST['confirm_pass']);

    if ($current !== $user['password']) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new_pass) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new_pass !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        mysqli_query($conn, "UPDATE users SET password = '$new_pass' WHERE id = $user_id");
        $success = "Password changed successfully!";
    }
}

$back_link = ($role == 'admin') ? 'admin.php' : 'student.php';
$back_label = ($role == 'admin') ? 'Admin Dashboard' : 'Student Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Student Assist</title>
    <link rel="stylesheet" href="auth_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="dashboard-body">

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-logo"><i class="fa-solid fa-book-open" style="color:#2563eb;"></i></div>
        <div class="sidebar-brand-text">
            <strong>Student Assist</strong>
            <small>Your Study Partner</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if ($role == 'admin'): ?>
        <a href="admin.php?section=dashboard" class="nav-item">
            <i class="fa-solid fa-gauge nav-icon"></i> Dashboard
        </a>
        <a href="admin.php?section=papers" class="nav-item">
            <i class="fa-solid fa-file-arrow-up nav-icon"></i> Past Paper Upload
        </a>
        <a href="admin.php?section=timetable" class="nav-item">
            <i class="fa-solid fa-calendar-days nav-icon"></i> Time Table Upload
        </a>
        <?php else: ?>
        <a href="student.php?section=dashboard" class="nav-item">
            <i class="fa-solid fa-gauge nav-icon"></i> Dashboard
        </a>
        <a href="student.php?section=timetable" class="nav-item">
            <i class="fa-solid fa-calendar-days nav-icon"></i> Timetable Viewer
        </a>
        <a href="student.php?section=assist" class="nav-item">
            <i class="fa-solid fa-brain nav-icon"></i> Assist
        </a>
        <a href="student.php?section=viewpaper" class="nav-item">
            <i class="fa-solid fa-file nav-icon"></i> View Paper
        </a>
        <a href="student.php?section=reminder" class="nav-item">
            <i class="fa-solid fa-bell nav-icon"></i> Reminder
        </a>
        <?php endif; ?>
        <a href="auth_profile.php" class="nav-item active">
            <i class="fa-solid fa-circle-user nav-icon"></i> My Profile
        </a>
    </nav>

    <a href="auth_logout.php" class="sidebar-logout">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</div>

<div class="main-content">

    <div class="topbar">
        <div class="topbar-left">
            <span class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('sidebar-open')">
                <i class="fa-solid fa-bars"></i>
            </span>
        </div>
        <div class="topbar-right">
            <i class="fa-solid fa-circle-user" style="font-size:18px; color:#2563eb;"></i>
            <span style="font-weight:600; color:#222;"><?php echo htmlspecialchars($user['full_name']); ?></span>
            <span class="role-badge role-<?php echo $role; ?>"><?php echo ucfirst($role); ?></span>
        </div>
    </div>

    <div class="content-area">
        <h2 class="section-title"><i class="fa-solid fa-circle-user"></i> My Profile</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="profile-layout">

            <div class="profile-left-panel">
                <div class="profile-avatar">
                    <i class="fa-solid fa-circle-user"></i>
                </div>
                <h3 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                <span class="role-badge role-<?php echo $role; ?> profile-role-badge"><?php echo ucfirst($role); ?></span>

                <div class="profile-info-list">
                    <div class="profile-info-item">
                        <i class="fa-solid fa-user"></i>
                        <div>
                            <small>Username</small>
                            <span><?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <i class="fa-solid fa-envelope"></i>
                        <div>
                            <small>Email</small>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <i class="fa-solid fa-calendar"></i>
                        <div>
                            <small>Member Since</small>
                            <span><?php echo date('d M Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <a href="<?php echo $back_link; ?>" class="btn-blue-outline" style="margin-top:20px; display:block; text-align:center;">
                    <i class="fa-solid fa-arrow-left"></i> <?php echo $back_label; ?>
                </a>
            </div>

            <div class="profile-right-panel">

                <div class="upload-card profile-form-card">
                    <h3 class="profile-form-title">
                        <i class="fa-solid fa-pen-to-square"></i> Update Profile Info
                    </h3>
                    <form method="POST">
                        <div class="form-group">
                            <label><i class="fa-solid fa-id-card"></i> Full Name</label>
                            <input type="text" name="full_name" class="form-input"
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-envelope"></i> Email Address</label>
                            <input type="email" name="email" class="form-input"
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <small class="field-hint">
                                <i class="fa-solid fa-circle-info"></i>
                                This email is used for password reset OTP delivery.
                            </small>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-user"></i> Username</label>
                            <input type="text" class="form-input"
                                   value="<?php echo htmlspecialchars($user['username']); ?>" disabled
                                   style="background:#f4f6fb; color:#888; cursor:not-allowed;">
                            <small class="field-hint"><i class="fa-solid fa-lock"></i> Username cannot be changed.</small>
                        </div>
                        <button type="submit" name="update_profile" class="btn-blue">
                            <i class="fa-solid fa-floppy-disk"></i> Save Changes
                        </button>
                    </form>
                </div>

                <div class="upload-card profile-form-card" style="margin-top:20px;">
                    <h3 class="profile-form-title">
                        <i class="fa-solid fa-lock"></i> Change Password
                    </h3>
                    <form method="POST">
                        <div class="form-group">
                            <label><i class="fa-solid fa-lock"></i> Current Password</label>
                            <div class="pw-wrap">
                                <input type="password" id="current_pass" name="current_pass"
                                       class="form-input" placeholder="Enter current password" required>
                                <button type="button" class="pw-toggle" onclick="togglePw('current_pass', this)" tabindex="-1">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-lock"></i> New Password</label>
                            <div class="pw-wrap">
                                <input type="password" id="new_pass" name="new_pass"
                                       class="form-input" placeholder="Minimum 6 characters" required>
                                <button type="button" class="pw-toggle" onclick="togglePw('new_pass', this)" tabindex="-1">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-lock"></i> Confirm New Password</label>
                            <div class="pw-wrap">
                                <input type="password" id="confirm_pass" name="confirm_pass"
                                       class="form-input" placeholder="Re-enter new password" required>
                                <button type="button" class="pw-toggle" onclick="togglePw('confirm_pass', this)" tabindex="-1">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn-blue">
                            <i class="fa-solid fa-key"></i> Change Password
                        </button>
                    </form>
                </div>

            </div><!-- end right panel -->
        </div><!-- end profile-layout -->
    </div><!-- end content-area -->
</div><!-- end main-content -->

<script>
function togglePw(inputId, btn) {
    var input = document.getElementById(inputId);
    var icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
        btn.title = 'Hide password';
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
        btn.title = 'Show password';
    }
}
</script>

</body>
</html>
