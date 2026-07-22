<?php
session_start();
require_once __DIR__ . '/auth_db.php';
require_once __DIR__ . '/auth_mailer.php';

if (!isset($conn) || !$conn) {
    die("Database connection is not available.");
}

$step    = 1;
$error   = '';
$success = '';

if (isset($_POST['send_otp'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $otp  = strval(rand(100000, 999999));

        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        mysqli_query($conn, "UPDATE users SET otp = '$otp' WHERE email = '$email'");

        $_SESSION['reset_email']   = $email;
        $_SESSION['reset_otp']     = $otp;
        $_SESSION['reset_expires'] = $expires;

        $send_result = sendOtpEmail($email, $user['full_name'], $otp);

        if ($send_result === true) {
            $success = "OTP sent to <strong>" . htmlspecialchars($email) . "</strong>. Check your inbox (and spam folder).";
            $step    = 2;
        } else {
            $error = "Could not send email. SMTP Error: <code>$send_result</code><br>
                      Please check your <strong>mail_config.php</strong> credentials.";
            $step  = 1;
            mysqli_query($conn, "UPDATE users SET otp = NULL WHERE email = '$email'");
        }
    } else {
        $error = "No account found with that email address.";
        $step  = 1;
    }
}

if (isset($_POST['verify_otp'])) {
    $entered_otp = mysqli_real_escape_string($conn, trim($_POST['otp']));

    if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_expires'])) {
        $error = "Session expired. Please start again.";
        $step  = 1;
    } elseif (time() > strtotime($_SESSION['reset_expires'])) {
        $error = "OTP has expired (10 minutes). Please request a new one.";
        $step  = 1;
        unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_expires']);
    } elseif ($entered_otp === $_SESSION['reset_otp']) {
        $step    = 3;
        $success = "<i class='fa-solid fa-circle-check'></i> OTP verified! Set your new password below.";
    } else {
        $error = "Incorrect OTP. Please try again.";
        $step  = 2;
    }
}

if (isset($_POST['reset_password'])) {
    $new_pass     = mysqli_real_escape_string($conn, $_POST['new_pass']);
    $confirm_pass = mysqli_real_escape_string($conn, $_POST['confirm_pass']);

    if (strlen($new_pass) < 6) {
        $error = "Password must be at least 6 characters.";
        $step  = 3;
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Passwords do not match.";
        $step  = 3;
    } else {
        $email = $_SESSION['reset_email'];
        mysqli_query($conn, "UPDATE users SET password = '$new_pass', otp = NULL WHERE email = '$email'");
        unset($_SESSION['reset_email'], $_SESSION['reset_otp'], $_SESSION['reset_expires']);
        $success = "<i class='fa-solid fa-circle-check'></i> Password reset successfully! <a href='auth_login.php'>Login Now &rarr;</a>";
        $step    = 1;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Student Assist</title>
    <link rel="stylesheet" href="auth_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="auth-body">

<div class="reset-steps-bar">
    <div class="reset-step <?php echo $step >= 1 ? 'step-done' : ''; ?>">
        <span class="step-num"><i class="fa-solid fa-envelope"></i></span>
        <span class="step-label">Email</span>
    </div>
    <div class="reset-step-line"></div>
    <div class="reset-step <?php echo $step >= 2 ? 'step-done' : ''; ?>">
        <span class="step-num"><i class="fa-solid fa-shield-halved"></i></span>
        <span class="step-label">OTP</span>
    </div>
    <div class="reset-step-line"></div>
    <div class="reset-step <?php echo $step >= 3 ? 'step-done' : ''; ?>">
        <span class="step-num"><i class="fa-solid fa-lock"></i></span>
        <span class="step-label">Reset</span>
    </div>
</div>

<?php if ($step == 1 || $step == 2): ?>
<div class="auth-card">
    <div class="auth-brand">
        <i class="fa-solid fa-book-open" style="color:#2563eb; font-size:22px;"></i>
        <span class="auth-brand-name">Study Assist</span>
    </div>
    <hr class="auth-divider">

    <?php if ($step == 1): ?>
    <h3 class="auth-card-title"><i class="fa-solid fa-key"></i> Forgot Password</h3>
    <p style="text-align:center;color:#666;font-size:13px;margin-bottom:18px;">Enter the email address linked to your account</p>
    <?php else: ?>
    <h3 class="auth-card-title"><i class="fa-solid fa-shield-halved"></i> Enter OTP</h3>
    <p style="text-align:center;color:#666;font-size:13px;margin-bottom:18px;">Check your inbox for the 6-digit code</p>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($step == 1): ?>
    <form method="POST">
        <div class="form-group">
            <label><i class="fa-solid fa-envelope"></i> Email Address</label>
            <input type="email" name="email" class="form-input" placeholder="yourname@email.com" required autofocus>
        </div>
        <button type="submit" name="send_otp" class="btn-blue-full">
            <i class="fa-solid fa-paper-plane"></i> Send OTP to Email
        </button>
    </form>
    <?php endif; ?>

    <?php if ($step == 2): ?>
    <form method="POST">
        <!-- Hidden field to keep step=2 on submit -->
        <input type="hidden" name="verify_otp_step" value="2">
        <div class="form-group">
            <label><i class="fa-solid fa-hashtag"></i> 6-Digit OTP Code</label>
            <input type="text" name="otp" class="form-input otp-input"
                   placeholder="_ _ _ _ _ _"
                   maxlength="6"
                   inputmode="numeric"
                   pattern="[0-9]{6}"
                   autocomplete="one-time-code"
                   required autofocus>
            <small class="otp-hint">
                <i class="fa-solid fa-clock"></i> Code expires in 10 minutes
            </small>
        </div>
        <button type="submit" name="verify_otp" class="btn-blue-full">
            <i class="fa-solid fa-circle-check"></i> Verify OTP
        </button>
        <form method="POST" style="margin-top:10px;">
            <button type="submit" name="send_otp" class="btn-resend">
                <i class="fa-solid fa-rotate"></i> Resend OTP
            </button>
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['reset_email'] ?? ''); ?>">
        </form>
    </form>
    <?php endif; ?>

    <p style="text-align:center; margin-top:18px;">
        <a href="auth_login.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
    </p>
</div>

<?php elseif ($step == 3): ?>
<div class="auth-card">
    <div class="auth-brand">
        <i class="fa-solid fa-book-open" style="color:#2563eb; font-size:22px;"></i>
        <span class="auth-brand-name">Study Assist</span>
    </div>
    <hr class="auth-divider">
    <h3 class="auth-card-title"><i class="fa-solid fa-unlock-keyhole"></i> Set New Password</h3>
    <p style="text-align:center; color:#555; font-size:13px; margin-bottom:18px;">Secure your academic journey</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label><i class="fa-solid fa-lock"></i> New Password</label>
            <div class="pw-wrap">
                <input type="password" id="new_pass" name="new_pass"
                       class="form-input" placeholder="Minimum 6 characters" required>
                <button type="button" class="pw-toggle" onclick="togglePw('new_pass', this)" tabindex="-1" title="Show password">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </div>
        <div class="form-group">
            <label><i class="fa-solid fa-lock"></i> Confirm Password</label>
            <div class="pw-wrap">
                <input type="password" id="confirm_pass" name="confirm_pass"
                       class="form-input" placeholder="Re-enter new password" required>
                <button type="button" class="pw-toggle" onclick="togglePw('confirm_pass', this)" tabindex="-1" title="Show password">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </div>
        <div class="reset-btn-row">
            <a href="auth_login.php" class="btn-blue-outline">
                <i class="fa-solid fa-arrow-left"></i> Cancel
            </a>
            <button type="submit" name="reset_password" class="btn-blue">
                <i class="fa-solid fa-rotate"></i> Reset Password
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

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

var otpInput = document.querySelector('.otp-input');
if (otpInput) {
    otpInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
}
</script>

</body>
</html>
